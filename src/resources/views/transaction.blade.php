@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/transaction.css') }}">
@endsection

@php
$hideHeaderNav = true;
$hideHeaderSearch = true;
@endphp

@section('content')
<main class="main">
    <div class="transaction-other">
        その他の取引
        @foreach ($sortedPurchases as $p)
        <a href="{{ route('transaction.show', $p->item->id) }}">
            <div class="transaction-other-item-name">
                {{ $p->item->item_name }}
            </div>
        </a>
        @endforeach
    </div>
    <div class="transaction-area">
        <div class="transaction-title">
            <div class="title-left">
                <div class="profile-image">
                    <img src="{{ asset('storage/' . $partner->image) }}" alt="profile_image">
                </div>
                <div class="transaction-name">
                    「{{ $partner->name }}」さんとの取引画面
                </div>
            </div>
            @if($purchase->user_id === auth()->id() && !$purchase->is_completed)
                <button type="button" class="transaction-button">
                    取引を完了する
                </button>
            @endif
        </div>
        <div class="transaction-content">
            <div class="item-image">
                <img src="{{ asset('storage/' . $purchase->item->item_image) }}" alt="item_image">
            </div>
            <div class="item-detail">
                <div class="item-name">
                    {{ $purchase->item->item_name }}
                </div>
                <div class="item-price">
                    ¥{{ number_format($purchase->item->price) }}
                </div>
            </div>
        </div>

        <div class="transaction-chat">
        @foreach ($messages as $msg)
            <div class="chat-row {{ $msg->sender_id === auth()->id() ? 'right' : 'left' }}">
                <div class="chat-header {{ $msg->sender_id === auth()->id() ? 'right' : '' }}">
                    @if($msg->sender_id !== auth()->id())
                        <div class="chat-icon">
                            @if($partner->image)
                                <img src="{{ asset('storage/' . $partner->image) }}">
                            @endif
                        </div>
                        <div class="chat-name">{{ $partner->name }}</div>
                    @else
                        <div class="chat-name">{{ auth()->user()->name }}</div>
                        <div class="chat-icon">
                            @if(Auth::user()->image)
                                <img src="{{ asset('storage/' . Auth::user()->image) }}">
                            @endif
                        </div>
                    @endif
                </div>
                <div class="chat-bubble {{ $msg->sender_id === auth()->id() ? 'right' : '' }}">
                    @if(request('edit') == $msg->id)
                        <form method="POST" action="{{ route('messages.update', $msg->id) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="text" name="message" value="{{ $msg->message }}" class="edit-input">
                            {{-- 既存画像表示 --}}
                            @if($msg->image_path)
                                <div class="edit-preview">
                                    <img src="{{ asset('storage/' . $msg->image_path) }}" width="100">
                                </div>
                            @endif
                            {{-- 画像差し替え --}}
                            <input type="file" name="image">
                            <div class="edit-actions">
                                <button type="submit" class="edit-save-button">保存</button>
                                <a href="{{ url()->current() }}" class="edit-close-button">
                                    閉じる
                                </a>
                            </div>
                        </form>
                    @else
                        {{ $msg->message }}
                    @endif
                </div>
                @if($msg->image_path)
                <div class="message-image {{ $msg->sender_id === auth()->id() ? 'right' : '' }}">
                    <img src="{{ asset('storage/' . $msg->image_path) }}">
                </div>
                @endif
                @if($msg->sender_id === auth()->id() && request('edit') != $msg->id)
                <div class="chat-edit">
                    <a href="{{ request()->fullUrlWithQuery(['edit' => $msg->id]) }}" class="edit-button">
                        編集
                    </a>
                    <form method="POST" action="{{ route('messages.destroy', $msg->id) }}">
                        @csrf
                        @method('DELETE')
                        <button class="delete-button" type="submit">削除</button>
                    </form>
                </div>
                @endif
            </div>
        @endforeach
        </div>

        <div class="send-message">
            <form method="POST" action="{{ route('messages.store') }}" enctype="multipart/form-data" class="send-form">
                @csrf
                <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">
                <input id="messageInput" class="input-message" name="message" placeholder="取引メッセージを記入してください">
                <div class="attach-image">
                    <label for="imageInput" class="attach-button">
                        画像を追加
                    </label>
                    <input type="file" id="imageInput" name="image" hidden>
                </div>
                <button type="submit" class="send-button">
                    <img src="{{ asset('img/sendbutton.jpg') }}">
                </button>
            </form>
            @if($errors->any())
                <div class="error-message">
                    @foreach($errors->all() as $error)
                        <span>{{ $error }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- モーダル -->
    <div id="ratingModal" class="modal-overlay {{ ($purchase->is_completed && !$alreadyRated) ? '' : 'hidden' }}">
        <div class="modal">
            <div class="modal-header">
                取引が完了しました。
            </div>
            <div class="modal-body">
                <p class="modal-text">今回の取引相手はどうでしたか？</p>
                <div class="star-rating-modal">
                    <span data-value="1">★</span>
                    <span data-value="2">★</span>
                    <span data-value="3">★</span>
                    <span data-value="4">★</span>
                    <span data-value="5">★</span>
                </div>
            </div>
            <form method="POST" action="{{ route('ratings.store') }}">
                @csrf
                <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">
                <input type="hidden" name="rating" id="ratingValue">
                <div class="modal-footer">
                    <button type="submit" class="modal-submit">送信する</button>
                </div>
            </form>
        </div>
    </div>

</main>
@endsection

@section('js')
<script src="{{ asset('js/message.js') }}"></script>
@endsection