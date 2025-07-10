<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MatrizBookUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;

    public function __construct($data)
    {
        // Log::info('MatrizBookUpdated construct', ['data' => $data]);
        $this->data = $data;
    }
    public function broadcastAs()
    {
        return 'MatrizBookUpdated';
    }


    public function broadcastOn()
    {
        // Log::info('MatrizBookUpdated event fired with channel');
        return [
            new Channel('market-data'),
        ];
    }
}