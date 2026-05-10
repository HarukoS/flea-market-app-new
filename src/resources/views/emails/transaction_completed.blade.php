@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/transaction_completed.css') }}">
@endsection

@php
$hideHeaderSearch = true;
$hideHeaderNav = true;
@endphp

@section('content')
<div class="email-content">
    <div class="email-content__text">
        {{ $purchase->item->user->name }} 様
        <br><br>

        いつもご利用いただきありがとうございます。
        <br><br>

        取引が完了しました。
        <br><br>

        【商品名】
        {{ $purchase->item->item_name }}
        <br><br>

        購入者が取引完了を行いました。
        <br>
        取引画面を開いて、購入者の評価をお願いいたします。
    </div>
</div>
@endsection