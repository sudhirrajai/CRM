<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\DiscussionGroup;
use App\Models\ProjectDiscussion;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class DiscussionGroupTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $staff;
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions so assignRole('admin') and Spatie middleware work correctly
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->staff = User::factory()->create();
        $this->staff->assignRole('staff');

        $this->client = User::factory()->create();
        $this->client->assignRole('client');
    }

    public function test_admin_can_create_group()
    {
        $this->actingAs($this->admin);

        $response = $this->postJson(route('discussion-groups.store'), [
            'name' => 'Admins & Staff Group',
            'user_ids' => [$this->staff->id]
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('group.name', 'Admins & Staff Group');

        $this->assertDatabaseHas('discussion_groups', [
            'name' => 'Admins & Staff Group',
            'created_by' => $this->admin->id
        ]);

        // Creator and staff should be members
        $group = DiscussionGroup::where('name', 'Admins & Staff Group')->first();
        $this->assertTrue($group->members()->where('users.id', $this->admin->id)->exists());
        $this->assertTrue($group->members()->where('users.id', $this->staff->id)->exists());
    }

    public function test_non_admin_cannot_create_group()
    {
        $this->actingAs($this->staff);

        $response = $this->postJson(route('discussion-groups.store'), [
            'name' => 'Staff Only Group',
            'user_ids' => []
        ]);

        $response->assertStatus(403);
    }

    public function test_member_can_view_group_messages()
    {
        $group = DiscussionGroup::create([
            'name' => 'Test Group',
            'created_by' => $this->admin->id
        ]);
        $group->members()->sync([$this->admin->id, $this->staff->id]);

        $this->actingAs($this->staff);

        $response = $this->getJson(route('groups.discussions.index', $group->id));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'discussions',
            'read_status',
            'project_members'
        ]);
    }

    public function test_non_member_cannot_view_group_messages()
    {
        $group = DiscussionGroup::create([
            'name' => 'Private Group',
            'created_by' => $this->admin->id
        ]);
        $group->members()->sync([$this->admin->id]);

        $this->actingAs($this->staff);

        $response = $this->getJson(route('groups.discussions.index', $group->id));

        $response->assertStatus(403);
    }

    public function test_member_can_post_message()
    {
        $group = DiscussionGroup::create([
            'name' => 'Test Group',
            'created_by' => $this->admin->id
        ]);
        $group->members()->sync([$this->admin->id, $this->staff->id]);

        $this->actingAs($this->staff);

        $response = $this->postJson(route('groups.discussions.store', $group->id), [
            'message' => 'Hello team!'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('project_discussions', [
            'group_id' => $group->id,
            'user_id' => $this->staff->id,
            'message' => 'Hello team!',
            'project_id' => null
        ]);
    }

    public function test_non_member_cannot_post_message()
    {
        $group = DiscussionGroup::create([
            'name' => 'Private Group',
            'created_by' => $this->admin->id
        ]);
        $group->members()->sync([$this->admin->id]);

        $this->actingAs($this->staff);

        $response = $this->postJson(route('groups.discussions.store', $group->id), [
            'message' => 'Sneaking in'
        ]);

        $response->assertStatus(403);
    }

    public function test_message_owner_can_update_message_within_time_window()
    {
        $group = DiscussionGroup::create([
            'name' => 'Test Group',
            'created_by' => $this->admin->id
        ]);
        $group->members()->sync([$this->admin->id, $this->staff->id]);

        $discussion = ProjectDiscussion::create([
            'group_id' => $group->id,
            'user_id' => $this->staff->id,
            'message' => 'Initial message'
        ]);

        $this->actingAs($this->staff);

        $response = $this->putJson(route('groups.discussions.update', [$group->id, $discussion->id]), [
            'message' => 'Updated message'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('project_discussions', [
            'id' => $discussion->id,
            'message' => 'Updated message',
            'is_edited' => true
        ]);
    }

    public function test_message_owner_cannot_update_message_after_time_window()
    {
        $group = DiscussionGroup::create([
            'name' => 'Test Group',
            'created_by' => $this->admin->id
        ]);
        $group->members()->sync([$this->admin->id, $this->staff->id]);

        $discussion = new ProjectDiscussion([
            'group_id' => $group->id,
            'user_id' => $this->staff->id,
            'message' => 'Initial message',
        ]);
        $discussion->timestamps = false;
        $discussion->created_at = Carbon::now()->subMinutes(11);
        $discussion->updated_at = Carbon::now()->subMinutes(11);
        $discussion->save();

        $this->actingAs($this->staff);

        $response = $this->putJson(route('groups.discussions.update', [$group->id, $discussion->id]), [
            'message' => 'Updated message'
        ]);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Edit time window (10 mins) has expired.']);
    }

    public function test_admin_can_assign_and_unassign_members()
    {
        $group = DiscussionGroup::create([
            'name' => 'Test Group',
            'created_by' => $this->admin->id
        ]);
        $group->members()->sync([$this->admin->id]);

        $this->actingAs($this->admin);

        // 1. Get available members
        $response = $this->getJson(route('groups.discussions.available-members', $group->id));
        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $this->staff->id]);

        // 2. Assign member
        $response = $this->postJson(route('groups.discussions.assign', $group->id), [
            'user_id' => $this->staff->id
        ]);
        $response->assertStatus(200);
        $this->assertTrue($group->members()->where('users.id', $this->staff->id)->exists());

        // 3. Unassign member
        $response = $this->deleteJson(route('groups.discussions.unassign', [$group->id, $this->staff->id]));
        $response->assertStatus(200);
        $this->assertFalse($group->members()->where('users.id', $this->staff->id)->exists());
    }

    public function test_non_admin_cannot_manage_members()
    {
        $group = DiscussionGroup::create([
            'name' => 'Test Group',
            'created_by' => $this->admin->id
        ]);
        $group->members()->sync([$this->admin->id, $this->staff->id]);

        $newUser = User::factory()->create();

        $this->actingAs($this->staff);

        // Try to assign
        $response = $this->postJson(route('groups.discussions.assign', $group->id), [
            'user_id' => $newUser->id
        ]);
        $response->assertStatus(403);

        // Try to unassign
        $response = $this->deleteJson(route('groups.discussions.unassign', [$group->id, $this->admin->id]));
        $response->assertStatus(403);
    }
}
