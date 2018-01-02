<?php

namespace App\Console\Commands;

use App\Models\Proxy;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
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
        $data = collect();
        for ($i = 1; $i <= 20; $i++) {
            $data = $data->merge($this->getProxy($i));
        }
        $data = $data->unique()->filter(function ($value) {
            return $value['time'] < 5;
        })->values();
        $this->save($data);
        $proxy = Proxy::where('status', 1)->orderBy('time')->take(50)->get();
        if (!empty($proxy)) {
            Cache::put('proxy_list', $proxy, 600);
        }
    }

    public function getProxy(int $page)
    {
       return  QueryList::get('https://proxy.coderbusy.com/zh-cn/classical/https-ready/p' . $page . '.aspx')
            ->find('tbody tr')->map(function ($value) {
                return [
                    'ip' => $value->find('td:eq(0)')->text(),
                    'port' => $value->find('td:eq(1)')->text(),
                    'time' => str_replace('秒', '', $value->find('td:eq(9)')->text()),
                ];
            });
    }
    private function save($proxyList){
        $client = new Client();
        try {
            $requests = function ($proxyList) use ($client) {
                foreach ($proxyList as $value) {
                    yield function () use ($client, $value) {
                        return $client->getAsync('http://myip.ipip.net/', [
                            'proxy' => [
                                'http' => 'tcp://' . $value['ip'] . ':' . $value['port'], // Use this proxy with "http"
                                'https' => 'tcp://' . $value['ip'] . ':' . $value['port'],
                            ],
                            'connect_timeout' => 2,
                            'timeout' => 3
                        ]);
                    };
                }
            };
            $pool = new Pool($client, $requests($proxyList), [
                'concurrency' => 5,
                'fulfilled' => function ($response, $index) use ($proxyList) {
                    $contents = $response->getBody()->getContents();
                    if (strpos($contents, $proxyList[$index]['ip']) !== false) {
                        Proxy::firstOrCreate(['ip'=>$proxyList[$index]['ip'],'port'=>$proxyList[$index]['port'],'status'=>1],['time'=>$proxyList[$index]['time'],'description' => $contents]);
                    }
                },
                'rejected' => function ($reason, $index) {
                    // this is delivered each failed request
                },
            ]);
            $promise = $pool->promise();
            $promise->wait();
        } catch (\Exception $e) {

        }
    }
}
