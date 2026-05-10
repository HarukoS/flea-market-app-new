<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * 名前が入力されていない場合、バリデーションメッセージが表示される
     */
    public function test_name_is_required()
    {
        // テスト手順1: 会員登録ページを開く
        $response = $this->get('/register');
        $response->assertStatus(200);

        // テスト手順2: 名前を入力せずに他の項目を入力する
        $formData = [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // テスト手順3: 登録ボタンを押す
        $response = $this->post('/register', $formData);

        // 期待挙動:「お名前を入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
        ]);
    }

    /**
     * @test
     * メールアドレスが入力されていない場合、バリデーションメッセージが表示される
     */
    public function test_email_is_required()
    {
        // テスト手順1: 会員登録ページを開く
        $response = $this->get('/register');
        $response->assertStatus(200);

        // テスト手順2: メールアドレスを入力せずに他の項目を入力する
        $formData = [
            'name' => 'test',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // テスト手順3: 登録ボタンを押す
        $response = $this->post('/register', $formData);

        // 期待挙動:「メールアドレスを入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /**
     * @test
     * パスワードが8文字未満の場合、バリデーションメッセージが表示される
     */
    public function test_password_must_be_at_least_8_characters()
    {
        // テスト手順1: 会員登録ページを開く
        $response = $this->get('/register');
        $response->assertStatus(200);

        // テスト手順2: 7文字以下のパスワードと他の必要項目を入力する
        $formData = [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'passwor',
            'password_confirmation' => 'passwor',
        ];

        // テスト手順3: 登録ボタンを押す
        $response = $this->post('/register', $formData);

        // 期待挙動:「パスワードは8文字以上で入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);
    }

    /**
     * @test
     * パスワードが確認用パスワードと一致しない場合、バリデーションメッセージが表示される
     */
    public function test_password_confirmation_must_match()
    {
        // テスト手順1: 会員登録ページを開く
        $response = $this->get('/register');
        $response->assertStatus(200);

        // テスト手順2: 確認用パスワードと異なるパスワードを入力し、他の必要項目も入力する
        $formData = [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password456',
        ];

        // テスト手順3: 登録ボタンを押す
        $response = $this->post('/register', $formData);

        // 期待挙動:「パスワードと一致しません」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors([
            'password_confirmation' => 'パスワードと一致しません',
        ]);
    }

    /**
     * @test
     * パスワードが入力されていない場合、バリデーションメッセージが表示される
     */
    public function test_password_is_required()
    {
        // テスト手順1: 会員登録ページを開く
        $response = $this->get('/register');
        $response->assertStatus(200);

        // テスト手順2: パスワードを入力せずに他の項目を入力する
        $formData = [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ];

        // テスト手順3: 登録ボタンを押す
        $response = $this->post('/register', $formData);

        // 期待挙動:「パスワードを入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /**
     * @test
     * 全ての項目が入力されている場合、会員情報が登録され、メール認証誘導画面に遷移される
     */
    public function test_user_is_registered_and_is_redirected_to_verification_page()
    {
        // テスト手順1: 会員登録ページを開く
        $response = $this->get('/register');
        $response->assertStatus(200);

        // テスト手順2: 全ての必要項目を正しく入力する
        $formData = [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // テスト手順3: 登録ボタンを押す
        $response = $this->post('/register', $formData);

        // 期待挙動1：会員情報が登録される
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        // 期待挙動2：メール認証誘導画面に遷移する
        $response->assertRedirect(route('verify.info'));
    }
}
