<?php

namespace App\Events;

use App\Models\Admin\HawanSession;
use App\Models\Admin\PoojaSession;
use App\Models\LiveSessionInvite;
use App\Support\LiveSessionSnapshot;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FamilyMemberStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public LiveSessionInvite $invite,
        public int $joinedCount
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('live-session.'.$this->sessionType().'.'.$this->invite->session_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'FamilyMemberStatusChanged';
    }

    public function broadcastWith(): array
    {
        $snapshot = $this->invite->booking ? LiveSessionSnapshot::make($this->invite->booking) : [];

        return [
            'joined_count' => $this->joinedCount,
            'snapshot' => $snapshot,
            'invite' => [
                'id' => $this->invite->id,
                'name' => $this->invite->name,
                'relation' => $this->invite->relation,
                'status' => $this->invite->statusLabel(),
                'joined_at' => $this->invite->joined_at?->format('d M Y, h:i A'),
                'left_at' => $this->invite->left_at?->format('d M Y, h:i A'),
            ],
        ];
    }

    private function sessionType(): string
    {
        return match ($this->invite->session_type) {
            HawanSession::class => 'hawan',
            PoojaSession::class => 'pooja',
        };
    }
}
