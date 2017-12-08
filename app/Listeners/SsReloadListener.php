<?php

namespace App\Listeners;

use App\Events\SsChange;
use App\Models\Shadowsocks;
use Illuminate\Contracts\Queue\ShouldQueue;

class SsReloadListener implements ShouldQueue
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
     * @param  SsChange $event
     * @return void
     */
    public function handle(SsChange $event)
    {
        $json = str_replace('"', '\"', json_encode([
            "port_password" => Shadowsocks::pluck('password', 'port'),
            "method" => "aes-256-cfb",
            "timeout" => 300
        ]));
        $cmd = "rm -rf /ss/ss.config && echo \"$json\" > /ss/ss.config && systemctl reload ss.service";
        $connection = ssh2_connect(config('vpn.host'), config('vpn.ssh.port'));
        ssh2_auth_password($connection, config('vpn.ssh.user'), config('vpn.ssh.password'));
        ssh2_exec($connection, $cmd);
    }
}
