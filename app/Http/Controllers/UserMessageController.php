<?php

namespace App\Http\Controllers;

use App\Models\ContactConversationMessage;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserMessageController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->isAdmin()) {
            return redirect()->route('admin.messages.index');
        }

        $threads = ContactMessage::with('latestConversationMessage')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_message_at')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('messages.index', compact('threads'));
    }

    public function show(Request $request, ContactMessage $contactMessage)
    {
        if ($request->user()->isAdmin()) {
            return redirect()->route('admin.messages.show', $contactMessage);
        }

        $this->authorizeOwner($request, $contactMessage);

        $contactMessage->update(['user_read_at' => now()]);
        $contactMessage->load(['conversationMessages.sender']);

        return view('messages.show', compact('contactMessage'));
    }

    public function storeReply(Request $request, ContactMessage $contactMessage)
    {
        if ($request->user()->isAdmin()) {
            return redirect()->route('admin.messages.show', $contactMessage);
        }

        $this->authorizeOwner($request, $contactMessage);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ], [
            'message.required' => 'Pesan wajib diisi.',
            'message.max' => 'Pesan maksimal 5000 karakter.',
        ]);

        $reply = $contactMessage->conversationMessages()->create([
            'sender_id' => $request->user()->id,
            'sender_type' => 'user',
            'message' => $data['message'],
        ]);

        $contactMessage->update([
            'read_at' => null,
            'user_read_at' => now(),
            'last_message_at' => $reply->created_at,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->messagePayload($reply),
            ], 201);
        }

        return redirect()
            ->route('messages.show', $contactMessage)
            ->with('success', 'Pesan berhasil dikirim.');
    }

    public function updates(Request $request, ContactMessage $contactMessage): JsonResponse
    {
        $this->authorizeOwner($request, $contactMessage);

        $data = $request->validate([
            'after_id' => ['nullable', 'integer', 'min:0'],
        ]);

        $messages = $contactMessage->conversationMessages()
            ->where('id', '>', $data['after_id'] ?? 0)
            ->orderBy('id')
            ->get();

        if ($messages->contains('sender_type', 'admin')) {
            $contactMessage->update(['user_read_at' => now()]);
        }

        return response()->json([
            'messages' => $messages->map(fn (ContactConversationMessage $message) => $this->messagePayload($message)),
        ]);
    }

    private function authorizeOwner(Request $request, ContactMessage $contactMessage): void
    {
        abort_unless($contactMessage->user_id === $request->user()->id, 403);
    }

    private function messagePayload(ContactConversationMessage $message): array
    {
        return [
            'id' => $message->id,
            'sender_type' => $message->sender_type,
            'sender_name' => $message->sender_type === 'admin' ? 'Admin InnoBit' : 'Anda',
            'message' => $message->message,
            'created_at' => $message->created_at->format('d M Y H:i'),
        ];
    }
}
