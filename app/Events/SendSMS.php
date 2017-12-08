<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SendSMS
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $phone;
    public $templates;
    public$tempData;

    /**
     * SendSMS constructor.
     * @param $phone
     * @param $templates
     * @param $tempData
     */
    public function __construct($phone, $templates, $tempData)
    {
        $this->phone = $phone;
        $this->templates = $templates;
        $this->tempData = $tempData;
    }


    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
