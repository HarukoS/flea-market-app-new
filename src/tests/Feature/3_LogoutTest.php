<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * ログアウトができる
     */
    public function test_logout()
    {
        // DBにユーザー作成（メール認証済み）
        $user = \App\Models\User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        // テスト手順1: ユーザーにログインする
        $response = $this->followingRedirects()->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);
        $this->assertAuthenticatedAs($user);

        // テスト手順2: ログアウトボタンを押す
        $response = $this->post('/logout');

        // 期待挙動: ログアウト処理が実行される
        $this->assertGuest();
    }
}
