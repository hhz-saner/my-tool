<?php

namespace App\Console\Commands;

use App\Models\Proxy;
use Cache;
use App\Models\ExtAliexpress;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Request;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class Aliexpress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aliexpress';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'aliexpress';

    private $proxy;
    private $client;
    private $userAgent = [
        "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; AcooBrowser; .NET CLR 1.1.4322; .NET CLR 2.0.50727)",
        "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; Acoo Browser; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; .NET CLR 3.0.04506)",
        "Mozilla/4.0 (compatible; MSIE 7.0; AOL 9.5; AOLBuild 4337.35; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)",
        "Mozilla/5.0 (Windows; U; MSIE 9.0; Windows NT 9.0; en-US)",
        "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Win64; x64; Trident/5.0; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 2.0.50727; Media Center PC 6.0)",
        "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 1.0.3705; .NET CLR 1.1.4322)",
        "Mozilla/4.0 (compatible; MSIE 7.0b; Windows NT 5.2; .NET CLR 1.1.4322; .NET CLR 2.0.50727; InfoPath.2; .NET CLR 3.0.04506.30)",
        "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN) AppleWebKit/523.15 (KHTML, like Gecko, Safari/419.3) Arora/0.3 (Change: 287 c9dfb30)",
        "Mozilla/5.0 (X11; U; Linux; en-US) AppleWebKit/527+ (KHTML, like Gecko, Safari/419.3) Arora/0.6",
        "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.2pre) Gecko/20070215 K-Ninja/2.1.1",
        "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9) Gecko/20080705 Firefox/3.0 Kapiko/3.0",
        "Mozilla/5.0 (X11; Linux i686; U;) Gecko/20070322 Kazehakase/0.4.5",
        "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.8) Gecko Fedora/1.9.0.8-1.fc10 Kazehakase/0.5.6",
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.56 Safari/535.11",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_3) AppleWebKit/535.20 (KHTML, like Gecko) Chrome/19.0.1036.7 Safari/535.20",
        "Opera/9.80 (Macintosh; Intel Mac OS X 10.6.8; U; fr) Presto/2.9.168 Version/11.52",
    ];

    /**
     * Create a new command instance.
     *
     * @param \GuzzleHttp\Client $client
     * @return void
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->proxy = Cache::remember('proxy_list',600,function(){
            return Proxy::where('status', 1)->orderBy('time')->take(50)->get();
        });
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $extAliexpress = ExtAliexpress::where('status', 0)->get();
        $client = new Client();
        $requests = function ($extAliexpress) use ($client) {
            foreach ($extAliexpress as $value) {
                yield function () use ($client, $value) {
                    $useProxy = $this->proxy->random();
                    return $client->getAsync('https://ru.aliexpress.com/wholesale?SearchText=' . $value->name, [
                        'headers' => [
                            'User-Agent' => $this->userAgent[array_rand($this->userAgent)],
                        ],
                        'proxy' => [
                            'http' => 'tcp://' . $useProxy['ip'] . ':' . $useProxy['port'], // Use this proxy with "http"
                            'https' => 'tcp://' . $useProxy['ip'] . ':' . $useProxy['port'],
                        ],
                        'connect_timeout' => 5,
                        'timeout' => 10
                    ]);
                };
            }
        };
        try {
            $pool = new Pool($client, $requests($extAliexpress), [
                'concurrency' => 5,
                'fulfilled' => function ($response, $index) use ($extAliexpress) {
                    $arr = [];
                    $pattern = "/\"enKeyword\":\".*?\"/i";
                    $contents = $response->getBody()->getContents();
                    if (preg_match($pattern, $contents, $arr)) {
                        $enKeyword = str_replace('"', '', explode(':', $arr[0])[1]);
                        $extAliexpress[$index]->update(['status' => 1, 'en_keyword' => $enKeyword]);
                    }
                },
                'rejected' => function ($reason, $index) {
                    // this is delivered each failed request
                },
            ]);
            // Initiate the transfers and create a promise
            $promise = $pool->promise();
            // Force the pool of requests to complete.
            $promise->wait();
        } catch (\Exception $e) {

        }
    }
}
