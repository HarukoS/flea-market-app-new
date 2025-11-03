<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Item;
use App\Models\Like;
use App\Models\Purchase;

class MyListTest extends TestCase
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
     * いいねした商品だけが表示される
     */
    public function test_only_liked_items_are_displayed()
    {
        // テスト手順1: ユーザーにログインする
        $user = User::first();

        $itemOwner = User::where('id', '!=', $user->id)->first();
        $likedItem = Item::where('user_id', $itemOwner->id)->first();

        Like::create([
            'user_id' => $user->id,
            'item_id' => $likedItem->id,
        ]);

        // テスト手順2: マイリストページを開く
        $response = $this->actingAs($user)->get('/?tab=mylist');
        $response->assertStatus(200);

        // 期待挙動: いいねをした商品が表示される
        $response->assertSee($likedItem->item_name);

        $unlikedItems = Item::where('user_id', '!=', $itemOwner->id)->get();
        foreach ($unlikedItems as $item) {
            $response->assertDontSee($item->item_name);
        }
    }

    /**
     * @test
     * 購入済み商品は「sold」と表示される
     */
    public function test_purchased_items_are_displayed_as_sold()
    {
        // テスト手順1: ユーザーにログインする
        $user = User::first();

        $itemOwner = User::where('id', '!=', $user->id)->first();
        $item = Item::where('user_id', $itemOwner->id)->first();

        Like::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        Purchase::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'payment_method' => 'credit_card',
            'postal_code' => '123-4567',
            'address' => '東京都新宿区1-1-1',
        ]);

        // テスト手順2、3: マイリストページを開き、購入済み商品を確認する
        $response = $this->actingAs($user)->get('/?tab=mylist');
        $response->assertStatus(200);

        // 期待挙動: 購入済み商品に「Sold」のラベルが表示される
        $response->assertSee('SOLD');
    }

    /**
     * @test
     * 未認証の場合は何も表示されない
     */
    public function test_no_items_are_displayed_for_unauthenticated_users()
    {
        // テスト手順1: マイリストページを開く
        $response = $this->get('/?tab=mylist');

        $response->assertStatus(200);

        // 期待挙動: 何も表示されない
        $response->assertViewHas('items', fn($items) => $items->isEmpty());
    }
}