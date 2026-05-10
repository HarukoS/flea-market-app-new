<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use App\Models\Category;
use App\Models\Condition;
use App\Models\Message;
use App\Models\Rating;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\ProfileRequest;
use App\Http\Requests\ExhibitionRequest;

class UsersController extends Controller
{
    /**
     * プロフィール編集画面表示
     */
    public function profile()
    {
        return view('profile');
    }

    /**
     * プロフィール編集
     */
    public function profileUpdate(ProfileRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $form = $request->except(['_token', 'image']);

        if ($request->hasFile('image')) {
            if ($user->image) {
                Storage::disk('public')->delete($user->image);
            }

            // 新しいファイル名を生成
            $extension = $request->file('image')->getClientOriginalExtension();
            $filename = 'UserId' . $user->id . '_' . $user->email . '.' . $extension;

            $path = $request->file('image')->storeAs('profile_images', $filename, 'public');

            $form['image'] = $path;
        }

        $user->update($form);

        $search = $request->input('search');
        $tab = $request->input('tab', 'recommend');

        $query = Item::query();

        if (!empty($search)) {
            $query->where('item_name', 'like', "%{$search}%");
        }

        if ($tab === 'mylists') {
            /** @var \App\Models\User|null $user */
            $user = Auth::user();

            if (!$user || !$user->hasVerifiedEmail()) {
                $items = collect();
                return view('index', compact('items', 'search', 'tab'));
            }

            // @noinspection PhpUndefinedMethodInspection
            /** @var \Illuminate\Support\Collection|\App\Models\Item[] $likedItemIds */
            $likedItemIds = $user->likedItems()->pluck('items.id');
            $query->whereIn('id', $likedItemIds);
        }

        /** @var \Illuminate\Support\Collection|\App\Models\Item[] $items */
        $items = $query->get();

        $items->each(function ($item) {
            $item->is_sold = Purchase::where('item_id', $item->id)->exists();
        });

        return view('index', compact('items', 'search', 'tab'));
    }

    /**
     * プロフィール画面表示
     */
    public function mypage(Request $request)
    {
        $search = $request->input('search');
        $page = $request->input('page', 'sell');
        $userId = Auth::id();

        $userRating = Rating::where('reviewee_id', $userId)
            ->avg('rating');

        $userRating = round($userRating ?? 0);

        $ratingPercent = ($userRating / 5) * 100;

        $unreadCount = Message::where('sender_id', '!=', $userId)
        ->whereHas('purchase', function ($q) use ($userId) {

            $q->whereDoesntHave('ratings', function ($ratingQuery) use ($userId) {
                $ratingQuery->where('reviewer_id', $userId);
            });

            $q->where(function ($query) use ($userId) {

                $query->where('user_id', $userId)

                ->orWhereHas('item', function ($q2) use ($userId) {
                    $q2->where('user_id', $userId);
                });
            });
        })
        ->count();

        if ($page === 'sell') {
            $tab = 'myitem';

            $items = Item::where('user_id', $userId)
                ->when($search, fn($q) => $q->where('item_name', 'like', "%{$search}%"))
                ->get();

        } elseif ($page === 'buy') {
            $tab = 'buy';

            $items = Item::whereHas('purchase', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->when($search, fn($q) => $q->where('item_name', 'like', "%{$search}%"))
                ->get();

        } elseif ($page === 'transaction') {
            $tab = 'transaction';

            $purchases = Purchase::with(['item', 'messages'])
                ->where(function ($q) use ($userId) {
                    $q->where('user_id', $userId)
                    ->orWhereHas('item', function ($q2) use ($userId) {
                        $q2->where('user_id', $userId);
                    });
                })
                ->whereDoesntHave('ratings', function ($q) use ($userId) {
                    $q->where('reviewer_id', $userId);
                })
                ->get();

            $purchases->each(function ($p) use ($userId) {
                $latestMessage = $p->messages
                    ->where('sender_id', '!=', $userId)
                    ->sortByDesc('created_at')
                    ->first();

                $p->latest_message_time = $latestMessage
                    ? $latestMessage->created_at
                    : null;
            });

            $sortedPurchases = $purchases->sort(function ($a, $b) {

                if ($a->latest_message_time && $b->latest_message_time) {
                    return $b->latest_message_time <=> $a->latest_message_time;
                }

                if ($a->latest_message_time) return -1;
                if ($b->latest_message_time) return 1;

                return $b->created_at <=> $a->created_at;
            });

            $items = $sortedPurchases->map(function ($p) use ($userId) {

                $item = $p->item;

                $item->message_count = $p->messages
                    ->where('sender_id', '!=', $userId)
                    ->count();

                return $item;
            });

            if (!empty($search)) {
                $items = $items->filter(function ($item) use ($search) {
                    return str_contains($item->item_name, $search);
                });
            }

        } else {
            $tab = '';
            $items = collect();
        }

        $items->each(function ($item) {
            $item->is_sold = Purchase::where('item_id', $item->id)->exists();
        });

        return view('mypage', compact(
            'items',
            'search',
            'tab',
            'unreadCount',
            'userRating',
            'ratingPercent'
        ));
    }

    /**
     * 商品出品画面表示
     */
    public function sellpage(Request $request)
    {
        $user = Auth::user();
        $categories = Category::all();
        $conditions = Condition::all();
        return view('sell', compact('user', 'categories', 'conditions'));
    }

    /**
     * 商品出品
     */
    public function sellitem(ExhibitionRequest $request)
    {
        $item = new Item();
        $item->item_name = $request->item_name;
        $item->brand_name = $request->brand_name;
        $item->description = $request->description;
        $item->price = $request->price;
        $item->condition_id = $request->condition;
        $item->user_id = auth()->id();
        $item->save();

        $item->categories()->sync($request->categories);

        $categoryNames = Category::whereIn('id', $request->categories)
            ->pluck('category_name_en')
            ->toArray();

        $categoryNameStr = implode('-', $categoryNames);

        if ($request->hasFile('item_image')) {
            $file = $request->file('item_image');
            $extension = $file->getClientOriginalExtension();

            $fileName = "ItemId{$item->id}_{$categoryNameStr}.{$extension}";

            $path = $file->storeAs('item_image', $fileName, 'public');

            $item->item_image = $path;
            $item->save();
        }

        $search = $request->input('search');
        $page = $request->input('page', 'sell');
        $userId = Auth::id();

        if ($page === 'sell') {
            $tab = 'myitem';
            $query = Item::where('user_id', $userId);
        } elseif ($page === 'buy') {
            $tab = 'buy';
            $query = Item::whereIn('id', function ($q) use ($userId) {
                $q->select('item_id')
                    ->from('purchases')
                    ->where('user_id', $userId);
            });
        } else {
            $tab = '';
            $query = Item::query();
        }

        if (!empty($search)) {
            $query->where('item_name', 'like', "%{$search}%");
        }

        $items = $query->get();

        $items->each(function ($item) {
            $item->is_sold = Purchase::where('item_id', $item->id)->exists();
        });

        $userRating = Rating::where('reviewee_id', $userId)
            ->avg('rating');

        $userRating = round($userRating ?? 0);

        $ratingPercent = ($userRating / 5) * 100;

        $unreadCount = Message::where('sender_id', '!=', $userId)
            ->whereHas('purchase', function ($q) use ($userId) {

                $q->whereDoesntHave('ratings', function ($ratingQuery) use ($userId) {
                    $ratingQuery->where('reviewer_id', $userId);
                });

                $q->where(function ($query) use ($userId) {

                    $query->where('user_id', $userId)

                    ->orWhereHas('item', function ($q2) use ($userId) {
                        $q2->where('user_id', $userId);
                    });
                });
            })
            ->count();

        return view('mypage', compact(
            'items',
            'search',
            'tab',
            'userRating',
            'ratingPercent',
            'unreadCount'
        ));
    }

    /**
     * 取引チャット画面表示
     */
    public function showTransaction($itemId)
    {
        $authId = auth()->id();

        $purchase = Purchase::with(['item.user', 'user'])
            ->where('item_id', $itemId)
            ->firstOrFail();

        $seller = $purchase->item->user;
        $buyer = $purchase->user;
        $partner = ($authId === $seller->id) ? $buyer : $seller;

        $messages = Message::where('purchase_id', $purchase->id)
            ->orderBy('created_at', 'asc')
            ->get();

        $alreadyRated = Rating::where('purchase_id', $purchase->id)
            ->where('reviewer_id', $authId)
            ->exists();

        $purchases = Purchase::with(['item', 'messages', 'ratings'])
            ->where(function ($q) use ($authId) {
                $q->where('user_id', $authId)
                ->orWhereHas('item', function ($q2) use ($authId) {
                    $q2->where('user_id', $authId);
                });
            })
            ->whereDoesntHave('ratings', function ($q) use ($authId) {
                $q->where('reviewer_id', $authId);
            })
            ->get();

        $purchases->each(function ($p) use ($authId) {
            $latestMessage = $p->messages
                ->where('sender_id', '!=', $authId)
                ->sortByDesc('created_at')
                ->first();

            $p->latest_message_time = $latestMessage
                ? $latestMessage->created_at
                : null;
        });

        $sortedPurchases = $purchases->sort(function ($a, $b) {

            if ($a->latest_message_time && $b->latest_message_time) {
                return $b->latest_message_time <=> $a->latest_message_time;
            }

            if ($a->latest_message_time) return -1;
            if ($b->latest_message_time) return 1;

            return $b->created_at <=> $a->created_at;
        });

        return view('transaction', compact(
            'purchase',
            'messages',
            'partner',
            'sortedPurchases',
            'alreadyRated'
        ));
    }
}
