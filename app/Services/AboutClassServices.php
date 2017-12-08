<?php
/**
 * Created by PhpStorm.
 * User: saner
 * Date: 2017/11/23
 * Time: 下午3:58
 */

namespace App\Services;

use Cache;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;

class AboutClassServices
{

    public function aboutClassList($date, $cache = true): Collection
    {
        if ($cache && Cache::has('aboutClass_' . $date)) {
            return Cache::get('aboutClass_' . $date);
        }
        $client = new Client();
        do {
            $response = $client->post('https://vcloud.keepyoga.com/course/api/wxccourselist', [
                'form_params' => [
                    'brand_id' => 5352,
                    'venue_id' => 1,
                    'date_str' => $date
                ]
            ]);
            if ($response->getStatusCode() != 200) {
                $return = false;
            } else {
                $data = json_decode($response->getBody()->getContents(), true);
                $return = collect($data['data']['list'])->map(function ($value) {
                    return [
                        'class_id' => $value['schedule_id'],
                        'class_name' => $value['name'],
                        'coach' => $value['coach'],
                        'coach_avatar' => $value['coach_avatar'],
                        'classroom' => $value['classroom'],
                        'date' => $value['date'],
                        'start_time' => str_replace($value['date'], '', $value['start_time']),
                        'end_time' => str_replace($value['date'], '', $value['end_time']),
                        'status' => $value['status'],
                        'remain_num' => $value['remain_num']
                    ];
                });
                $minutes = Carbon::now()->diffInMinutes(Carbon::createFromFormat('Y-m-d H:i:s', $date . ' 00:00:00')->addDay());
                Cache::put('aboutClass_' . $date, $return, $minutes);
            }
        } while (!$return);
        return $return;
    }
}