<?php

namespace App\Events;

use App\Models\Pandit\PanditMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PanditMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public PanditMessage $message
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(
                'pandit.chat.' . $this->message->pandit_id
            ),
        ];
    }

    public function broadcastAs(): string
    {
        return 'pandit.message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'pandit_id' => $this->message->pandit_id,
                'sender' => $this->message->sender,
                'message' => $this->message->message,
                'created_at' => $this->message->created_at
                    ->format('d M, h:i A'),
            ],
        ];
    }
}