<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * メールアドレスが入力されていない場合、バリデーションメッセージが表示される
     */
    public function test_email_is_required()
    {
        // テスト手順1: ログインページを開く
        $response = $this->get('/login');
        $response->assertStatus(200);

        // テスト手順2: メールアドレスを入力せずに他の項目を入力する
        $formData = [
            'email' => '',
            'password' => 'password123',
        ];

        // テスト手順3: ログインボタンを押す
        $response = $this->post('/login', $formData);

        // 期待挙動:「メールアドレスを入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /**
     * @test
     * パスワードが入力されていない場合、バリデーションメッセージが表示される
     */
    public function test_password_is_required()
    {
        // テスト手順1: ログインページを開く
        $response = $this->get('/login');
        $response->assertStatus(200);

        // テスト手順2: パスワードを入力せずに他の項目を入力する
        $formData = [
            'email' => 'test@example.com',
            'password' => '',
        ];

        // テスト手順3: ログインボタンを押す
        $response = $this->post('/login', $formData);

        // 期待挙動:「パスワードを入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /**
     * @test
     * 入力情報が間違っている場合、バリデーションメッセージが表示される
     */
    public function test_invalid_credentials()
    {
        // テスト手順1: ログインページを開く
        $response = $this->get('/login');
        $response->assertStatus(200);

        // テスト手順2: 必要項目を登録されていない情報を入力する
        $formData = [
            'email' => 'notfound@example.com',
            'password' => 'wrongpassword',
        ];

        // テスト手順3: ログインボタンを押す
        $response = $this->post('/login', $formData);

        // 期待挙動:「ログイン情報が登録されていません」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }

    /**
     * @test
     * 正しい情報が入力された場合、ログイン処理が実行される
     */
    public function test_user_can_login_with_valid_credentials()
    {
        // DBにユーザー作成（メール認証済み）
        $user = \App\Models\User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        // テスト手順1: ログインページを開く
        $response = $this->get('/login');
        $response->assertStatus(200);

        // テスト手順2、3: 全ての必要項目を入力し、ログインボタンを押す
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // 期待挙動: ログイン処理が実行される
        $this->assertAuthenticatedAs($user);
    }
}
