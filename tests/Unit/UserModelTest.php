<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_user_with_uuid()
    {
        $user = User::factory()->create();
//        $user = User::create([
//            'name' => 'Test User',
//            'email' => 'test@example.com',
//            'password' => bcrypt('password123'),
//            'location' => 'Moscow',
//        ]);

        $this->assertNotNull($user->id);
        $this->assertMatchesRegularExpression('/^[a-f0-9-]{36}$/', $user->id);
    }

    #[Test]
    public function it_has_fillable_fields()
    {
        $fillable = (new User())->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
        $this->assertContains('phone', $fillable);
        $this->assertContains('location', $fillable);
        $this->assertContains('birth_date', $fillable);
    }

    #[Test]
    public function it_hides_password_and_remember_token()
    {
        $hidden = (new User())->getHidden();

        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);
    }

    #[Test]
    public function it_casts_birth_date_to_date()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'birth_date' => '1990-05-15',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->birth_date);
    }
}
