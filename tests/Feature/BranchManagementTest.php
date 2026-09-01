<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function room(string $branch): Room
    {
        return Room::create([
            'slug'   => 'phong-' . uniqid(),
            'name'   => 'Phòng thử',
            'branch' => $branch,
            'price'  => 500000,
            'status' => 'active',
        ]);
    }

    public function test_the_seeded_branches_are_the_old_hardcoded_ones(): void
    {
        $this->assertSame(['Hotel', 'Villa', 'Residence'], Branch::names());
    }

    public function test_admin_can_add_a_branch_and_use_it_for_a_room(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->postJson('/admin/api/branches', ['name' => 'Resort', 'label' => 'Khu nghỉ dưỡng', 'color' => '#aa3311'])
            ->assertCreated();

        $this->assertContains('Resort', Branch::names());

        $this->actingAs($admin)
            ->postJson('/admin/api/rooms', [
                'name'   => 'Bungalow view hồ',
                'slug'   => 'bungalow-view-ho',
                'branch' => 'Resort',
                'price'  => 1200000,
                'status' => 'active',
            ])
            ->assertCreated();

        $this->assertSame('Resort', Room::where('name', 'Bungalow view hồ')->value('branch'));
    }

    public function test_a_room_cannot_use_an_unknown_branch(): void
    {
        $this->actingAs($this->admin())
            ->postJson('/admin/api/rooms', [
                'name'   => 'Phòng lạ',
                'slug'   => 'phong-la',
                'branch' => 'Không có thật',
                'price'  => 100000,
                'status' => 'active',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('branch');
    }

    public function test_renaming_a_branch_moves_its_rooms(): void
    {
        $room   = $this->room('Villa');
        $branch = Branch::where('name', 'Villa')->first();

        $this->actingAs($this->admin())
            ->putJson('/admin/api/branches/' . $branch->id, ['name' => 'Villa Đà Lạt', 'color' => '#7c3aed'])
            ->assertOk();

        $this->assertSame('Villa Đà Lạt', $room->fresh()->branch);
    }

    public function test_a_branch_with_rooms_cannot_be_deleted(): void
    {
        $this->room('Hotel');
        $branch = Branch::where('name', 'Hotel')->first();

        $this->actingAs($this->admin())
            ->deleteJson('/admin/api/branches/' . $branch->id)
            ->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('branches', ['name' => 'Hotel']);
    }

    public function test_an_empty_branch_can_be_deleted(): void
    {
        $branch = Branch::where('name', 'Residence')->first();

        $this->actingAs($this->admin())
            ->deleteJson('/admin/api/branches/' . $branch->id)
            ->assertOk();

        $this->assertDatabaseMissing('branches', ['name' => 'Residence']);
    }

    public function test_employees_cannot_manage_branches(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $this->actingAs($employee)
            ->postJson('/admin/api/branches', ['name' => 'Lén thêm'])
            ->assertForbidden();
    }
}
