<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pandit\Pandit;
use App\Models\Pandit\PanditMessage;
use Illuminate\Http\Request;

use App\Events\PanditMessageSent;

class AdminMessageController extends Controller
{
    // All pandits who have messages
    public function index()
    {
        $pandits = Pandit::whereHas('messages')->withCount([
            'messages as unread_count' => fn($q) => $q->where('sender', 'pandit')->where('is_read', false)
        ])->latest()->get();

        return view('admin.messages.index', compact('pandits'));
    }

    // Chat with a specific pandit
    public function show($panditId)
    {
        $pandit = Pandit::findOrFail($panditId);
        $messages = PanditMessage::where('pandit_id', $panditId)->oldest()->get();

        // mark pandit messages as read
        PanditMessage::where('pandit_id', $panditId)
            ->where('sender', 'pandit')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('admin.messages.show', compact('pandit', 'messages'));
    }

    // Admin reply
    public function reply(Request $request, $panditId)
    {
        $request->validate(['message' => 'required|string|max:1000']);

        $message = PanditMessage::create([
            'pandit_id' => $panditId,
            'sender'    => 'admin',
            'message'   => $request->message,
        ]);
        // PanditMessage::create([
        //     'pandit_id' => $panditId,
        //     'sender'    => 'admin',
        //     'message'   => $request->message,
        // ]);
        broadcast(new PanditMessageSent($message));

        return back()->with('success', 'Reply sent!');
    }

    // Delete a message
    public function delete($id)
    {
        PanditMessage::findOrFail($id)->delete();
        return back()->with('success', 'Message deleted!');
    }
}
