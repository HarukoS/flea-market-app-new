@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/mypage.css') }}">
@endsection

@section('content')
<div class="profile-area">
    <div class="profile-image">
        @if(Auth::user()->image)
            <img src="{{ asset('storage/' . Auth::user()->image) }}" alt="profile_image">
        @endif
    </div>

    <div class="profile-info">
        <div class="profile-name">
            {{ auth()->user()->name }}
        </div>

        <div class="star-rating">
            <div class="star-rating-front" data-width="{{ $ratingPercent }}">
                ★★★★★
            </div>
            <div class="star-rating-back">
                ★★★★★
            </div>
        </div>
    </div>

    <div class="profile-edit">
        <a href="{{ route('profile') }}">プロフィールを編集</a>
    </div>
</div>

<div class="items-page">

    <ul class="items-tabs">
        <li class="tab {{ $tab === 'myitem' ? 'active' : '' }}">
            <a href="{{ route('mypage', ['page' => 'sell', 'search' => $search]) }}">
                出品した商品
            </a>
        </li>
        <li class="tab {{ $tab === 'buy' ? 'active' : '' }}">
            <a href="{{ route('mypage', ['page' => 'buy', 'search' => $search]) }}">
                購入した商品
            </a>
        </li>
        <li class="tab {{ $tab === 'transaction' ? 'active' : '' }}">
            <a href="{{ route('mypage', ['page' => 'transaction', 'search' => $search]) }}">
                取引中の商品
            </a>
        </li>
        <div class="message">
            <div class="message-number">{{ $unreadCount }}</div>
        </div>
    </ul>

    <div class="items-grid" id="itemsGrid">
        @foreach ($items as $item)
        <div class="item-card">
            <a href="{{ $tab === 'transaction'? route('transaction.show', $item->id): route('items.show', $item->id) }}" class="item-image-wrapper">
                <img src="{{ $item->image_url }}" alt="item_image">
                {{-- メッセージバッジ --}}
                @if($tab === 'transaction' && $item->message_count > 0)
                <div class="message-badge">{{ $item->message_count }}</div>
                @endif
                @if($tab !== 'transaction' && $item->is_sold)
                    <div class="sold-ribbon">SOLD</div>
                @endif
            </a>
            <p class="item-name">{{ $item->item_name }}</p>
        </div>
        @endforeach
    </div>

</div>
@endsection

@section('js')
<script>
document.querySelectorAll('.star-rating-front').forEach(el => {
    el.style.width = el.dataset.width + '%';
});
</script>
@endsection