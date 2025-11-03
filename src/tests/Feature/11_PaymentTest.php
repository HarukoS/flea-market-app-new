<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
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
     * 小計画面で変更が反映される
     */
    public function test_selected_payment_method_is_applied()
    {
        $user = User::first();
        $item = Item::first();

        // テスト手順1: 支払い方法選択画面を開く
        $response = $this->actingAs($user)->get(route('purchase.page', $item->id));
        $response->assertStatus(200);
        $response->assertSee($item->item_name);

        // テスト手順2: プルダウンメニューから支払い方法を選択する
        $postData = [
            'item_id' => $item->id,
            'payment_method' => 'カード支払い',
            'postal_code' => $user->postal_code ?? '100-0001',
            'address' => $user->address ?? '東京都千代田区1-1',
            'building' => $user->building ?? '',
        ];

        $response = $this->actingAs($user)
            ->post(route('purchase.pre', ['item' => $item->id]), $postData);

        $response->assertRedirect();
        $follow = $this->actingAs($user)->get($response->headers->get('Location'));

        // 期待挙動：選択した支払い方法が正しく反映される
        $follow->assertSee('カード支払い');
    }
}
