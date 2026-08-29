<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveSessionInvite extends Model
{
    protected $fillable = [
        'session_type',
        'session_id',
        'name',
        'relation',
        'token_hash',
        'expires_at',
        'revoked_at',
        'joined_at',
        'left_at',
        'last_seen_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->morphTo(__FUNCTION__, 'session_type', 'session_id');
    }

    public function isValid(): bool
    {
        return !$this->revoked_at && (!$this->expires_at || $this->expires_at->isFuture());
    }

    public function statusLabel(): string
    {
        if ($this->left_at) {
            return 'Left';
        }

        if ($this->joined_at) {
            return 'Joined';
        }

        return 'Invited';
    }
}
