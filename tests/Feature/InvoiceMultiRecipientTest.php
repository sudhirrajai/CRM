<?php

namespace Tests\Feature;

use App\Mail\HostingSuspensionMail;
use App\Models\Client;
use App\Models\ClientHosting;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class InvoiceMultiRecipientTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_invoice_with_shared_clients_and_extra_recipients(): void
    {
        DB::table('roles')->insert([
            'id' => (string) Str::uuid(),
            'name' => 'admin',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $currency = Currency::create([
            'name' => 'US Dollar',
            'code' => 'USD',
            'symbol' => '$',
            'symbol_position' => 'prefix',
            'decimal_places' => 2,
        ]);

        $primaryClient = Client::create([
            'name' => 'Primary Client',
            'email' => 'primary@example.com',
            'status' => 'active',
            'currency_id' => $currency->id,
        ]);

        $partnerClient = Client::create([
            'name' => 'Partner Client',
            'email' => 'partner@example.com',
            'status' => 'active',
            'currency_id' => $currency->id,
        ]);

        $response = $this->actingAs($admin)->post(route('invoices.store'), [
            'client_id' => $primaryClient->id,
            'shared_client_ids' => [$partnerClient->id],
            'extra_recipients' => ['accounts@example.com', 'billing@example.com'],
            'currency_id' => $currency->id,
            'invoice_number' => 'INV-MULTI-001',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'total_amount' => 1200.00,
            'status' => 'draft',
            'items' => [
                [
                    'description' => 'Consulting Services',
                    'quantity' => 1,
                    'unit_price' => 1200.00,
                    'total' => 1200.00,
                ],
            ],
        ]);

        $response->assertRedirect(route('invoices.index'));

        $invoice = Invoice::where('invoice_number', 'INV-MULTI-001')->firstOrFail();

        $this->assertDatabaseHas('invoice_clients', [
            'invoice_id' => $invoice->id,
            'client_id' => $partnerClient->id,
        ]);

        $this->assertDatabaseHas('invoice_email_recipients', [
            'invoice_id' => $invoice->id,
            'email' => 'accounts@example.com',
        ]);
    }

    public function test_shared_client_can_view_invoice_in_portal(): void
    {
        $currency = Currency::create([
            'name' => 'US Dollar',
            'code' => 'USD',
            'symbol' => '$',
            'symbol_position' => 'prefix',
            'decimal_places' => 2,
        ]);

        $ownerClient = Client::create([
            'name' => 'Owner Client',
            'email' => 'owner@example.com',
            'status' => 'active',
            'currency_id' => $currency->id,
        ]);

        $sharedClient = Client::create([
            'name' => 'Shared Client',
            'email' => 'shared@example.com',
            'status' => 'active',
            'currency_id' => $currency->id,
        ]);

        $invoice = Invoice::create([
            'client_id' => $ownerClient->id,
            'currency_id' => $currency->id,
            'invoice_number' => 'INV-SHARED-001',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(5)->toDateString(),
            'sub_total' => 500,
            'tax' => 0,
            'total_amount' => 500,
            'status' => 'sent',
        ]);
        $invoice->sharedClients()->attach($sharedClient->id);

        $sharedUser = User::factory()->create([
            'client_id' => $sharedClient->id,
        ]);

        $this->actingAs($sharedUser)
            ->get(route('invoices.show', $invoice->id))
            ->assertOk();
    }

    public function test_suspension_email_is_sent_to_all_invoice_recipients(): void
    {
        DB::table('roles')->insert([
            'id' => (string) Str::uuid(),
            'name' => 'admin',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $currency = Currency::create([
            'name' => 'US Dollar',
            'code' => 'USD',
            'symbol' => '$',
            'symbol_position' => 'prefix',
            'decimal_places' => 2,
        ]);

        $ownerClient = Client::create([
            'name' => 'Owner Client',
            'email' => 'owner@example.com',
            'status' => 'active',
            'currency_id' => $currency->id,
        ]);

        $partnerClient = Client::create([
            'name' => 'Partner Client',
            'email' => 'partner@example.com',
            'status' => 'active',
            'currency_id' => $currency->id,
        ]);

        $project = Project::create([
            'client_id' => $ownerClient->id,
            'name' => 'Shared Service',
            'status' => 'in_progress',
        ]);
        $server = Server::create([
            'name' => 'Test Server',
            'provider' => 'Local',
            'ip_address' => '127.0.0.1',
            'credentials' => ['user' => 'root', 'password' => 'secret'],
            'monthly_cost' => 100,
            'currency_id' => $currency->id,
            'renewal_date' => now()->addMonth()->toDateString(),
            'status' => 'active',
        ]);

        $invoice = Invoice::create([
            'client_id' => $ownerClient->id,
            'project_id' => $project->id,
            'currency_id' => $currency->id,
            'invoice_number' => 'INV-SUSP-001',
            'issue_date' => now()->subDays(20)->toDateString(),
            'due_date' => now()->subDays(10)->toDateString(),
            'sub_total' => 1000,
            'tax' => 0,
            'total_amount' => 1000,
            'status' => 'sent',
        ]);
        $invoice->sharedClients()->attach($partnerClient->id);
        $invoice->emailRecipients()->create(['email' => 'accounts@example.com']);

        ClientHosting::create([
            'client_id' => $ownerClient->id,
            'server_id' => $server->id,
            'project_id' => $project->id,
            'domain' => 'example.com',
            'price' => 1000,
            'currency_id' => $currency->id,
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);

        Mail::fake();

        $this->actingAs($admin)
            ->post(route('invoices.send-suspension', $invoice->id))
            ->assertRedirect();

        Mail::assertSent(HostingSuspensionMail::class, function (HostingSuspensionMail $mail) {
            return $mail->hasTo('owner@example.com')
                && $mail->hasTo('partner@example.com')
                && $mail->hasTo('accounts@example.com');
        });
    }
}
