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
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        
        $this->client = User::factory()->create();
        // Assume client role assignment logic exists or manually add if not
        
        $this->project = Project::factory()->create(['client_id' => $this->client->client_id ?? null]);
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
        
        $discussion = ProjectDiscussion::create([
            'project_id' => $this->project->id,
            'user_id' => $this->admin->id,
            'message' => 'Initial message',
            'created_at' => Carbon::now()->subMinutes(11)
        ]);

        $response = $this->putJson(route('projects.discussions.update', [$this->project->id, $discussion->id]), [
            'message' => 'Updated message'
        ]);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Edit time window (10 mins) has expired.']);
    }
}
