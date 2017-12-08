<?php

namespace App\Listeners;

use PhpSms;
use App\Events\SendSMS;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendSMSListener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  SendSMS  $event
     * @return void
     */
    public function handle(SendSMS $event)
    {
        \Log::info('SendSMS|'.$event->phone.'|'.json_encode($event->templates).'|'.json_encode($event->tempData));
        PhpSms::make()->to($event->phone)->template($event->templates)->data($event->tempData)->send();
    }
}
