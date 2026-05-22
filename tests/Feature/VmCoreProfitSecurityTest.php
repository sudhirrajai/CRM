<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class VmCoreProfitSecurityTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $clientUser;
    private $currency;
    private $client;
    private $project;
    private $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin and client roles
        DB::table('roles')->insert([
            [
                'id' => (string) Str::uuid(),
                'name' => 'admin',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'client',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->currency = Currency::create([
            'name' => 'US Dollar',
            'code' => 'USD',
            'symbol' => '$',
            'symbol_position' => 'prefix',
            'decimal_places' => 2,
        ]);

        $this->client = Client::create([
            'name' => 'Security Test Client',
            'email' => 'client@security-test.com',
            'status' => 'active',
            'currency_id' => $this->currency->id,
        ]);

        $this->clientUser = User::factory()->create([
            'client_id' => $this->client->id,
        ]);
        $this->clientUser->assignRole('client');

        // Create a project with internal/sensitive data
        $this->project = Project::create([
            'client_id' => $this->client->id,
            'name' => 'Secret Project',
            'status' => 'in_progress',
            'budget' => 10000.00,
            'vmcore_profit' => 2500.00,
            'internal_notes' => 'Highly confidential vmcore margin notes.',
        ]);

        // Create an invoice with internal/sensitive data
        $this->invoice = Invoice::create([
            'client_id' => $this->client->id,
            'project_id' => $this->project->id,
            'currency_id' => $this->currency->id,
            'invoice_number' => 'INV-SEC-999',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'sub_total' => 10000.00,
            'tax' => 0.00,
            'total_amount' => 10000.00,
            'vmcore_profit' => 2500.00,
            'status' => 'paid',
        ]);
    }

    public function test_admin_can_see_project_internal_notes_and_profit(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('projects.show', $this->project->id));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('project.vmcore_profit', 2500)
            ->where('project.internal_notes', 'Highly confidential vmcore margin notes.')
        );
    }

    public function test_client_cannot_see_project_internal_notes_and_profit(): void
    {
        $response = $this->actingAs($this->clientUser)
            ->get(route('projects.show', $this->project->id));

        $response->assertOk();
        
        // Assert fields are missing from serialization entirely for clients
        $response->assertInertia(fn ($page) => $page
            ->missing('project.vmcore_profit')
            ->missing('project.internal_notes')
        );
    }

    public function test_client_project_index_does_not_leak_internal_notes_or_profit(): void
    {
        $response = $this->actingAs($this->clientUser)
            ->get(route('projects.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('projects.0', fn ($page) => $page
                ->missing('vmcore_profit')
                ->missing('internal_notes')
                ->etc()
            )
        );
    }

    public function test_admin_can_see_invoice_profit(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('invoices.show', $this->invoice->id));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('invoice.vmcore_profit', 2500)
        );
    }

    public function test_client_cannot_see_invoice_profit(): void
    {
        $response = $this->actingAs($this->clientUser)
            ->get(route('invoices.show', $this->invoice->id));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->missing('invoice.vmcore_profit')
        );
    }

    public function test_client_invoice_index_does_not_leak_profit(): void
    {
        $response = $this->actingAs($this->clientUser)
            ->get(route('invoices.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('invoices.0', fn ($page) => $page
                ->missing('vmcore_profit')
                ->etc()
            )
        );
    }
}
