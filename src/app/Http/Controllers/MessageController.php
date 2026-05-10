<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\MessageRequest;

class MessageController extends Controller
{

    public function store(MessageRequest $request)
    {
        $path = null;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('message_images', 'public');
        }

        Message::create([
            'purchase_id' => $request->purchase_id,
            'sender_id' => Auth::id(),
            'message' => $request->message,
            'image_path' => $path,
        ]);

        return back();
    }

    public function update(MessageRequest $request, Message $message)
    {
        if ($message->sender_id !== Auth::id()) {
            abort(403);
        }

        $data = [
            'message' => $request->message
        ];

        if ($request->hasFile('image')) {

            if ($message->image_path) {
                Storage::disk('public')->delete($message->image_path);
            }

            $path = $request->file('image')->store('message_images', 'public');
            $data['image_path'] = $path;
        }

        $message->update($data);

        return back();
    }

    public function destroy(Message $message)
    {
        if ($message->sender_id !== Auth::id()) {
            abort(403);
        }

        $message->delete();

        return back();
    }

    public function show($itemId)
    {
        $purchase = Purchase::with(['item.user'])->where('item_id', $itemId)->firstOrFail();

        $messages = Message::where('purchase_id', $purchase->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('transaction', compact('purchase', 'messages'));
    }
}
