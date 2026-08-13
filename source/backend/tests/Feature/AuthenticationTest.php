<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_username_and_role(): void
    {
        $user = $this->makeUser('student');

        $response = $this->postJson('/api/v1/auth/login', [
            'identifier' => $user->username,
            'password' => 'edunova123',
            'role' => 'student',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('user.username', $user->username)
            ->assertJsonPath('user.role', 'student');

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_password_is_rejected(): void
    {
        $user = $this->makeUser('student');

        $this->postJson('/api/v1/auth/login', [
            'identifier' => $user->username,
            'password' => 'salah-password',
            'role' => 'student',
        ])->assertUnprocessable();
    }

    public function test_wrong_role_is_rejected(): void
    {
        $user = $this->makeUser('student');

        $this->postJson('/api/v1/auth/login', [
            'identifier' => $user->username,
            'password' => 'edunova123',
            'role' => 'admin',
        ])->assertUnprocessable();
    }

    public function test_authenticated_user_can_read_profile(): void
    {
        $user = $this->makeUser('teacher');

        $this->actingAs($user)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('user.role', 'teacher');
    }

    public function test_role_middleware_blocks_other_roles(): void
    {
        $user = $this->makeUser('student');

        $this->actingAs($user)
            ->getJson('/api/v1/admin/ping')
            ->assertForbidden();
    }

    private function makeUser(string $role): User
    {
        $school = School::query()->create([
            'name' => 'Sekolah Test',
            'code' => 'TEST-'.strtoupper($role),
            'slug' => 'test-'.$role,
            'timezone' => 'Asia/Jakarta',
            'is_active' => true,
        ]);

        return User::factory()->create([
            'school_id' => $school->id,
            'username' => $role.'.test',
            'role' => $role,
            'status' => 'active',
            'subtitle' => ucfirst($role),
            'password' => 'edunova123',
        ]);
    }
}
