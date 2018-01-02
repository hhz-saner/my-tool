<?php

namespace App\Console\Commands;

use App\Models\Proxy;
use GuzzleHttp\Client;
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
    protected $signature = 'getProxy';

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
        Proxy::where('status', 1)->update(['status' => 0]);
        for ($i = 1; $i <= 10; $i++) {
            $this->getProxy($i);
        }
        $proxy = Proxy::where('status', 1)->orderBy('time')->take(50)->get();
        if (!empty($proxy)) {
            Cache::put('proxy_list', $proxy, 600);
        }
    }

    public function getProxy(int $page)
    {
        QueryList::get('https://proxy.coderbusy.com/zh-cn/classical/https-ready/p' . $page . '.aspx')
            ->find('tbody tr')->map(function ($value) {
                return [
                    'ip' => $value->find('td:eq(0)')->text(),
                    'port' => $value->find('td:eq(1)')->text(),
                    'time' => str_replace('秒', '', $value->find('td:eq(9)')->text()),
                ];
            })->filter(function ($value) {
                return $value['time'] < 5;
            })->each(function ($value) {
                if ($contents = $this->test($value)) {
                    Proxy::create(array_merge($value, ['description' => $contents]));
                }
            });
    }

    public function test($proxy)
    {
        $client = new Client();
        try {
            $response = $client->get('http://myip.ipip.net/', [
                'proxy' => [
                    'http' => 'tcp://' . $proxy['ip'] . ':' . $proxy['port'], // Use this proxy with "http"
                    'https' => 'tcp://' . $proxy['ip'] . ':' . $proxy['port']
                ],
                'connect_timeout' => 3,
                'timeout' => 3
            ]);
            $contents = $response->getBody()->getContents();
            if (strpos($contents, $proxy['ip']) === false) {
                return false;
            }
            return $contents;
        } catch (\Exception $e) {
            return false;
        }
    }
}
