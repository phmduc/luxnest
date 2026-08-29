<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaImportUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_import(): void
    {
        $this->postJson('/admin/api/media-library/import-url', ['url' => 'https://example.com/a.jpg'])
            ->assertStatus(401);
    }

    public function test_admin_gets_a_readable_error_for_a_bad_link(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->postJson('/admin/api/media-library/import-url', ['url' => 'file:///etc/passwd']);

        $response->assertStatus(422);
        $this->assertFalse($response->json('success'));
        $this->assertStringContainsString('http', (string) $response->json('message'));
    }

    public function test_admin_import_rejects_private_addresses(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->postJson('/admin/api/media-library/import-url', ['url' => 'http://127.0.0.1/secret.jpg'])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }
}
