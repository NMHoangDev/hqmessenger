<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class Message implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $message;



    /**
     * Create a new event instance.
     */
    public function __construct($message)
    {
        //
        $this->message = $message;
        //      
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('message.' . $this->message->to_id), // example is message.7
        ];
    }
    function broadcastWith(): array
    {
        return [
            "id" => $this->message->id,
            "message" => $this->message->body,
            "to_id" => $this->message->to_id,
            "attachment" => $this->message->attachment,
            "from_id" => auth()->user()->id
        ];
    }
}
