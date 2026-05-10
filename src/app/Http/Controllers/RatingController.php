<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rating;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\TransactionCompletedMail;

class RatingController extends Controller
{
    /**
     * 取引後評価送信、取引完了
     */
    public function store(Request $request)
    {
        $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $purchase = Purchase::with('item')->findOrFail($request->purchase_id);

        $authId = Auth::id();

        $revieweeId = ($purchase->user_id === $authId)
            ? $purchase->item->user_id
            : $purchase->user_id;

        Rating::create([
            'purchase_id' => $purchase->id,
            'reviewer_id' => $authId,
            'reviewee_id' => $revieweeId,
            'rating' => $request->rating,
        ]);

        if ($purchase->user_id === $authId) {

            $seller = $purchase->item->user;

            Mail::to($seller->email)
                ->send(new TransactionCompletedMail($purchase));
        }

        $purchase->update([
            'is_completed' => true
        ]);

        return redirect()->route('index')
            ->with('success', '評価を送信しました');
    }
}
