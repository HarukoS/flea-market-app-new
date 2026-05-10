<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\MessageRequest;

class MessageController extends Controller
{
    /**
     * 取引メッセージ登録
     */
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

    /**
     * 取引メッセージ編集
     */
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

    /**
     * 取引メッセージ削除
     */
    public function destroy(Message $message)
    {
        if ($message->sender_id !== Auth::id()) {
            abort(403);
        }

        $message->delete();

        return back();
    }
}
