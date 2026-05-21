<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Models\ProjectDiscussion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class ProjectDiscussionTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $client;
    protected $project;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed Spatie roles so assigning role works correctly
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        
        $currency = \App\Models\Currency::create([
            'name' => 'US Dollar',
            'code' => 'USD',
            'symbol' => '$',
            'symbol_position' => 'prefix',
            'decimal_places' => 2,
        ]);

        $clientRecord = \App\Models\Client::create([
            'name' => 'Test Client',
            'email' => 'client@example.com',
            'status' => 'active',
            'currency_id' => $currency->id,
        ]);

        $this->client = User::factory()->create([
            'client_id' => $clientRecord->id
        ]);
        
        $this->project = Project::create([
            'client_id' => $clientRecord->id,
            'name' => 'Test Project',
            'status' => 'in_progress',
        ]);
    }

    public function test_admin_can_post_message()
    {
        $this->actingAs($this->admin);
        
        $response = $this->postJson(route('projects.discussions.store', $this->project->id), [
            'message' => 'Hello from admin'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('project_discussions', [
            'message' => 'Hello from admin',
            'user_id' => $this->admin->id
        ]);
    }

    public function test_edit_window_constraint()
    {
        $this->actingAs($this->admin);
        
        $discussion = new ProjectDiscussion([
            'project_id' => $this->project->id,
            'user_id' => $this->admin->id,
            'message' => 'Initial message',
        ]);
        $discussion->timestamps = false;
        $discussion->created_at = Carbon::now()->subMinutes(11);
        $discussion->updated_at = Carbon::now()->subMinutes(11);
        $discussion->save();

        $response = $this->putJson(route('projects.discussions.update', [$this->project->id, $discussion->id]), [
            'message' => 'Updated message'
        ]);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Edit time window (10 mins) has expired.']);
    }
}
