<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Item;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed([
            'Database\Seeders\UsersTableSeeder',
            'Database\Seeders\ConditionsTableSeeder',
            'Database\Seeders\ItemsTableSeeder',
        ]);
    }

    /**
     * @test
     * ログイン済みのユーザーはコメントを送信できる
     */
    public function test_authenticated_user_can_submit_comment()
    {
        // テスト手順1: ユーザーにログインする
        $user = User::first();
        $item = Item::first();

        $initialCommentCount = $item->comments()->count();

        // テスト手順2: コメントを入力する
        $commentText = 'テストコメント';

        // テスト手順3: コメントボタンを押す
        $response = $this->actingAs($user)->post('/comment', [
            'item_id' => $item->id,
            'comment' => $commentText,
        ]);

        $response->assertStatus(200);

        // 期待挙動: コメントが保存され、コメント数が増加する
        $this->assertDatabaseHas('comments', [
            'item_id' => $item->id,
            'user_id' => $user->id,
            'comment' => $commentText,
        ]);
        $response = $this->actingAs($user)->get(route('items.show', $item->id));
        $response->assertStatus(200);
        $response->assertSee('<span class="comment-number">' . ($initialCommentCount + 1) . '</span>', false);
    }

    /**
     * @test
     * ログイン前のユーザーはコメントを送信できない
     */
    public function test_guest_cannot_submit_comment()
    {
        // テスト手順1: コメントを入力する
        $item = Item::first();
        $commentText = '未ログインコメント';

        // テスト手順2: コメントボタンを押す
        $response = $this->post('/comment', [
            'item_id' => $item->id,
            'comment' => $commentText,
        ]);

        // 期待挙動：コメントが送信されない
        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('comments', [
            'item_id' => $item->id,
            'comment' => $commentText,
        ]);
    }

    /**
     * @test
     * コメントが入力されていない場合、バリデーションメッセージが表示される
     */
    public function test_comment_is_required()
    {
        // テスト手順1: ユーザーにログインする
        $user = User::first();
        $item = Item::first();

        // テスト手順2: コメントボタンを押す
        $response = $this->actingAs($user)->post('/comment', [
            'item_id' => $item->id,
            'comment' => '',
        ]);

        // 期待挙動: バリデーションメッセージが表示される
        $response->assertRedirect(route('items.show', $item->id));
        $response->assertSessionHasErrors(['comment' => 'コメントを入力してください']);
    }

    /**
     * @test
     * コメントが255字以上の場合、バリデーションメッセージが表示される
     */
    public function test_comment_cannot_exceed_255_characters()
    {
        // テスト手順1: ユーザーにログインする
        $user = User::first();
        $item = Item::first();

        // テスト手順2: 255文字以上のコメントを入力する
        $longComment = str_repeat('あ', 256);

        // テスト手順3: コメントボタンを押す
        $response = $this->actingAs($user)->post('/comment', [
            'item_id' => $item->id,
            'comment' => $longComment,
        ]);

        // 期待挙動: バリデーションメッセージが表示される
        $response->assertRedirect(route('items.show', $item->id));
        $response->assertSessionHasErrors(['comment' => 'コメントは255文字以下で入力してください']);
    }
}
