<?php

namespace App\Jobs;

use App\Models\AutomationEnrollment;
use App\Models\AutomationStep;
use App\Models\Setting;
use App\Mail\AutomationEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ProcessAutomationStepJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function __construct(
        public AutomationEnrollment $enrollment,
        public AutomationStep $step
    ) {}

    public function handle(): void
    {
        // Ensure enrollment is still active
        $this->enrollment->refresh();
        if ($this->enrollment->status !== 'active') {
            return;
        }

        // Ensure the automation is still active
        $automation = $this->enrollment->automation;
        if ($automation->status !== 'active') {
            return;
        }

        try {
            switch ($this->step->action_type) {
                case 'send_email':
                    $this->handleSendEmail();
                    break;

                case 'wait':
                    $this->handleWait();
                    return; // Don't advance to next step yet

                case 'condition':
                    // Future: evaluate condition rules
                    break;
            }

            // Advance to next step
            $this->advanceToNextStep();

        } catch (\Exception $e) {
            Log::error("Automation step failed: {$e->getMessage()}", [
                'enrollment_id' => $this->enrollment->id,
                'step_id' => $this->step->id,
            ]);

            if ($this->attempts() >= $this->tries) {
                $this->enrollment->update(['status' => 'cancelled']);
            } else {
                throw $e;
            }
        }
    }

    private function handleSendEmail(): void
    {
        $template = $this->step->emailTemplate;
        if (!$template) {
            Log::warning("Automation step {$this->step->id} has no email template.");
            return;
        }

        $enrollable = $this->enrollment->enrollable;
        $email = $this->getEnrollableEmail($enrollable);

        if (!$email) {
            Log::warning("Could not determine email for enrollable {$this->enrollment->enrollable_type}:{$this->enrollment->enrollable_id}");
            return;
        }

        // Configure SMTP
        $host = Setting::getValue('smtp_host');
        if ($host) {
            config([
                'mail.mailers.smtp.host' => $host,
                'mail.mailers.smtp.port' => Setting::getValue('smtp_port', 587),
                'mail.mailers.smtp.username' => Setting::getValue('smtp_username'),
                'mail.mailers.smtp.password' => Setting::getValue('smtp_password'),
                'mail.mailers.smtp.encryption' => Setting::getValue('smtp_encryption', 'tls'),
                'mail.from.address' => Setting::getValue('smtp_from_address'),
                'mail.from.name' => Setting::getValue('smtp_from_name'),
            ]);
        }

        Mail::to($email)->send(new AutomationEmail($template, $enrollable));
    }

    private function handleWait(): void
    {
        $delayHours = $this->step->wait_duration ?? 1;

        // Re-dispatch this job with a delay, but for the next step
        $nextStep = $this->step->nextStep();
        if ($nextStep) {
            $this->enrollment->update(['current_step_id' => $nextStep->id]);
            ProcessAutomationStepJob::dispatch($this->enrollment, $nextStep)
                ->delay(now()->addHours($delayHours));
        } else {
            // No more steps, mark as completed
            $this->enrollment->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }
    }

    private function advanceToNextStep(): void
    {
        $nextStep = $this->step->nextStep();

        if ($nextStep) {
            $this->enrollment->update(['current_step_id' => $nextStep->id]);
            ProcessAutomationStepJob::dispatch($this->enrollment, $nextStep);
        } else {
            $this->enrollment->update([
                'status' => 'completed',
                'completed_at' => now(),
                'current_step_id' => null,
            ]);
        }
    }

    private function getEnrollableEmail($enrollable): ?string
    {
        if ($enrollable instanceof \App\Models\Lead) {
            return $enrollable->contact_email;
        }

        if ($enrollable instanceof \App\Models\Client) {
            return $enrollable->email;
        }

        return $enrollable->email ?? null;
    }
}
