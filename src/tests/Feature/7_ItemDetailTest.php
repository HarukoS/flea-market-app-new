<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Item;
use App\Models\User;
use App\Models\Like;
use App\Models\Comment;
use App\Models\Category;

class ItemDetailTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed([
            'Database\Seeders\UsersTableSeeder',
            'Database\Seeders\ConditionsTableSeeder',
            'Database\Seeders\ItemsTableSeeder',
            'Database\Seeders\CategoriesTableSeeder',
        ]);
    }

    /**
     * @test
     * 必要な情報が表示される
     */
    public function test_required_information_is_displayed()
    {
        $user = User::first();
        $item = Item::with(['categories', 'condition', 'likes', 'comments'])->first();

        Like::firstOrCreate([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        if ($item->comments->count() === 0) {
            Comment::create([
                'user_id' => $user->id,
                'item_id' => $item->id,
                'comment' => 'テストコメントです',
            ]);
            $item->load('comments');
        }

        // テスト手順1: 商品詳細ページを開く
        $response = $this->actingAs($user)->get(route('items.show', ['id' => $item->id]));
        $response->assertStatus(200);

        // 期待挙動：すべての情報が商品詳細ページに表示されている
        //商品画像
        $response->assertSee($item->image_url);
        //商品名
        $response->assertSee($item->item_name);
        //ブランド名
        $response->assertSee($item->brand_name ?? '');
        //価格
        $response->assertSee(number_format($item->price));
        //いいね数
        $response->assertSee((string) $item->likes->count());
        //コメント数
        $response->assertSee((string) $item->comments->count());
        //商品説明
        $response->assertSee($item->description);
        //商品の状態
        $response->assertSee($item->condition->condition_name);
        //カテゴリー
        foreach ($item->categories as $category) {
            $response->assertSee($category->category_name);
        }
        //コメントしたユーザー情報、コメント内容
        foreach ($item->comments as $comment) {
            $response->assertSee($comment->user->name);
            $response->assertSee($comment->comment);
        }
    }

    /**
     * @test
     * 複数選択されたカテゴリが表示されているか
     */
    public function test_multiple_selected_categories_are_displayed()
    {
        $user = User::first();
        $item = Item::with('categories')->first();

        if ($item->categories->count() < 2) {
            $categories = Category::take(2)->get();
            $item->categories()->sync($categories->pluck('id'));
            $item->refresh();
        }

        // テスト手順1: 商品詳細ページを開く
        $response = $this->actingAs($user)->get(route('items.show', ['id' => $item->id]));
        $response->assertStatus(200);

        // 期待挙動：複数選択されたカテゴリが商品詳細ページに表示されている
        foreach ($item->categories as $category) {
            $response->assertSee($category->category_name);
        }
    }
}
