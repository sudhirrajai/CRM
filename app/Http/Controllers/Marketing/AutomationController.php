<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\MarketingAutomation;
use App\Models\AutomationStep;
use App\Models\AutomationEnrollment;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AutomationController extends Controller
{
    public function index()
    {
        $automations = MarketingAutomation::with('creator')
            ->withCount(['steps', 'enrollments', 'enrollments as active_enrollments_count' => function ($q) {
                $q->where('status', 'active');
            }])
            ->latest()
            ->get()
            ->map(function ($automation) {
                $automation->trigger_label = $automation->trigger_label;
                return $automation;
            });

        return Inertia::render('Marketing/Automations/Index', [
            'automations' => $automations,
            'triggerEvents' => MarketingAutomation::TRIGGER_EVENTS,
        ]);
    }

    public function create()
    {
        return Inertia::render('Marketing/Automations/Builder', [
            'automation' => null,
            'triggerEvents' => MarketingAutomation::TRIGGER_EVENTS,
            'templates' => EmailTemplate::select('id', 'name', 'subject')->latest()->get(),
            'leadStages' => \App\Models\LeadPipelineStage::ordered()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trigger_event' => 'required|string|in:' . implode(',', array_keys(MarketingAutomation::TRIGGER_EVENTS)),
            'trigger_conditions' => 'nullable|array',
            'steps' => 'nullable|array',
            'steps.*.action_type' => 'required|in:send_email,wait,condition',
            'steps.*.email_template_id' => 'nullable|exists:email_templates,id',
            'steps.*.wait_duration' => 'nullable|integer|min:1',
        ]);

        $automation = MarketingAutomation::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'trigger_event' => $validated['trigger_event'],
            'trigger_conditions' => $validated['trigger_conditions'] ?? null,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        // Create steps
        if (!empty($validated['steps'])) {
            foreach ($validated['steps'] as $index => $stepData) {
                AutomationStep::create([
                    'automation_id' => $automation->id,
                    'order' => $index,
                    'action_type' => $stepData['action_type'],
                    'email_template_id' => $stepData['email_template_id'] ?? null,
                    'wait_duration' => $stepData['wait_duration'] ?? null,
                ]);
            }
        }

        return redirect()->route('marketing.automations.index')
            ->with('success', 'Automation created successfully.');
    }

    public function show($id)
    {
        return $this->edit($id);
    }

    public function edit($id)
    {
        $automation = MarketingAutomation::with(['steps.emailTemplate', 'enrollments' => function ($q) {
            $q->with('enrollable')->latest()->limit(50);
        }])->findOrFail($id);

        return Inertia::render('Marketing/Automations/Builder', [
            'automation' => $automation,
            'triggerEvents' => MarketingAutomation::TRIGGER_EVENTS,
            'templates' => EmailTemplate::select('id', 'name', 'subject')->latest()->get(),
            'leadStages' => \App\Models\LeadPipelineStage::ordered()->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $automation = MarketingAutomation::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trigger_event' => 'required|string|in:' . implode(',', array_keys(MarketingAutomation::TRIGGER_EVENTS)),
            'trigger_conditions' => 'nullable|array',
            'steps' => 'nullable|array',
            'steps.*.action_type' => 'required|in:send_email,wait,condition',
            'steps.*.email_template_id' => 'nullable|exists:email_templates,id',
            'steps.*.wait_duration' => 'nullable|integer|min:1',
        ]);

        $automation->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'trigger_event' => $validated['trigger_event'],
            'trigger_conditions' => $validated['trigger_conditions'] ?? null,
        ]);

        // Replace steps
        $automation->steps()->delete();
        if (!empty($validated['steps'])) {
            foreach ($validated['steps'] as $index => $stepData) {
                AutomationStep::create([
                    'automation_id' => $automation->id,
                    'order' => $index,
                    'action_type' => $stepData['action_type'],
                    'email_template_id' => $stepData['email_template_id'] ?? null,
                    'wait_duration' => $stepData['wait_duration'] ?? null,
                ]);
            }
        }

        return redirect()->route('marketing.automations.index')
            ->with('success', 'Automation updated successfully.');
    }

    public function destroy($id)
    {
        $automation = MarketingAutomation::findOrFail($id);
        $automation->delete();

        return redirect()->route('marketing.automations.index')
            ->with('success', 'Automation deleted successfully.');
    }

    /**
     * Activate an automation.
     */
    public function activate($id)
    {
        $automation = MarketingAutomation::findOrFail($id);

        if ($automation->steps()->count() === 0) {
            return redirect()->back()->with('error', 'Cannot activate an automation with no steps.');
        }

        $automation->update(['status' => 'active']);

        return redirect()->back()->with('success', 'Automation activated.');
    }

    /**
     * Deactivate an automation.
     */
    public function deactivate($id)
    {
        $automation = MarketingAutomation::findOrFail($id);
        $automation->update(['status' => 'inactive']);

        return redirect()->back()->with('success', 'Automation deactivated.');
    }
}
