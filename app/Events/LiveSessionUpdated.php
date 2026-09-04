<?php

namespace App\Events;

use App\Models\Admin\HawanSession;
use App\Models\Admin\PoojaSession;
use App\Support\LiveSessionSnapshot;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveSessionUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public Model $booking)
    {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('live-session.'.$this->type().'.'.$this->booking->id)];
    }

    public function broadcastAs(): string
    {
        return 'LiveSessionUpdated';
    }

    public function broadcastWith(): array
    {
        return LiveSessionSnapshot::make($this->booking);
    }

    private function type(): string
    {
        return $this->booking instanceof HawanSession ? 'hawan' : ($this->booking instanceof PoojaSession ? 'pooja' : 'unknown');
    }
}
