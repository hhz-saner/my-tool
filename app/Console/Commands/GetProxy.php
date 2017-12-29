<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use QL\QueryList;
use Illuminate\Support\Facades\Cache;

class GetProxy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:getProxy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'get proxy';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $data = collect([]);
        for ($i = 1; $i < 5; $i++) {
            $data = $data->merge($this->getProxy($i));
        }
        $data =  $data->sortBy('time')->values()->all();
        Cache::put('proxy_ip_list', $data, 10);
    }

    public function getProxy(int $page)
    {
        return QueryList::get('https://proxy.coderbusy.com/zh-cn/classical/https-ready/p' . $page . '.aspx')
            ->find('tbody tr')->map(function ($value) {
                return [
                    'ip' => $value->find('td:eq(0)')->text(),
                    'proxy' => $value->find('td:eq(1)')->text(),
                    'time' => str_replace('秒', '', $value->find('td:eq(9)')->text()),
                ];
            });
    }
}
