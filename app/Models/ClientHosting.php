<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Mail;
use App\Mail\HostingSuspensionMail;
use App\Mail\HostingTerminatedMail;
use App\Models\Invoice;

class ClientHosting extends Model
{
    use HasUuids;

    protected $fillable = ['client_id', 'server_id', 'project_id', 'domain', 'plan_details', 'price', 'currency_id', 'billing_cycle', 'next_due_date', 'status', 'reason'];
    
    protected function casts(): array
    {
        return ['next_due_date' => 'date'];
    }
    
    public function client() { return $this->belongsTo(Client::class); }
    public function server() { return $this->belongsTo(Server::class); }
    public function currency() { return $this->belongsTo(Currency::class); }
    public function project() { return $this->belongsTo(Project::class); }

    protected static function booted()
    {
        static::updated(function ($hosting) {
            if ($hosting->isDirty('status')) {
                $status = $hosting->status;
                $reason = $hosting->reason;
                
                if ($status === 'suspended') {
                    // Find the latest unpaid invoice for this hosting
                    $invoice = Invoice::where('project_id', $hosting->project_id)
                        ->where('client_id', $hosting->client_id)
                        ->where('status', '!=', 'paid')
                        ->latest()
                        ->first();
                    
                    if ($invoice) {
                        Mail::to($hosting->client->email)->send(new \App\Mail\HostingSuspensionMail($invoice, $hosting, $reason));
                    }
                } elseif ($status === 'terminated') {
                    Mail::to($hosting->client->email)->send(new \App\Mail\HostingTerminatedMail($hosting->domain, $hosting->client->name, $reason));
                } elseif ($status === 'cancelled') {
                    Mail::to($hosting->client->email)->send(new \App\Mail\HostingCancelledMail($hosting->domain, $hosting->client->name, $reason));
                }
            }
        });
    }
}
