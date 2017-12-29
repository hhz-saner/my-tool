<?php
/**
 * Created by PhpStorm.
 * User: saner
 * Date: 2017/10/27
 * Time: 下午2:52
 */

namespace App\Http\Controllers\Api;

use App\Models\Analytics;
use Exception;
use Illuminate\Http\Request;

class AnalyticsController extends ApiController
{
    public function index(Request $request){
        $data = $request->all();
        $analytics = new Analytics();
        $analytics->data = json_encode($data);
        $analytics->type = $request->input('type');
        $analytics->key = $request->input('key');
        $analytics->title =$request->input('title');
        $analytics->ip = $request->ip();
        $analytics->referer = $request->server('http_referer');
        $analytics->save();
        return response('')->header('Content-Type', 'application/javascript');
    }
}