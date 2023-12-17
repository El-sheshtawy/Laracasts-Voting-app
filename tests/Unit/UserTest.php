<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_check_if_user_is_an_admin()
    {
        $user = User::factory()->create([
            'email' => 'ramyalfe22@gmail.com',
        ]);

        $userB = User::factory()->create([
            'email' => 'user@gmail.com',
        ]);

        $this->assertTrue($user->isAdmin());
        $this->assertFalse($userB->isAdmin());
    }
}
