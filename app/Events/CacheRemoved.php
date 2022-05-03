<?php

namespace App\Events;

use Diagro\Events\BroadcastWhenOccupied;
use Illuminate\Queue\SerializesModels;

class CacheRemoved
{
    use SerializesModels, BroadcastWhenOccupied;



    public function __construct(public array $keys_tags, $user_id)
    {
        $this->user_id = $user_id;
    }

    public function broadcastAs(): string
    {
        return "deleted";
    }

    protected function channelName(): string
    {
        return "Diagro.Cache";
    }
}
