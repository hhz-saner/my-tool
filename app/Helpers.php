<?php
if (!function_exists('setting')) {
    function setting()
    {

    }
}

if (!function_exists('formatBytes')) {
    function formatBytes($size, $precision = 2)
    {
        if ($size > 0) {
            $size = (int)$size;
            $base = log($size) / log(1024);
            $suffixes = [' bytes', ' KB', ' MB', ' GB', ' TB'];
            return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
        } else {
            return $size;
        }
    }
}

if (!function_exists('mArrayUnique')) {
    function mArrayUnique($array)
    {
        $return = array();
        foreach ($array as $key => $v) {
            if (!in_array($v, $return)) {
                $return[$key] = $v;
            }
        }
        return $return;
    }
}

//if (!function_exists('isWorkDay')) {
//    function isWorkDay()
//    {
//        $now = \Carbon\Carbon::now();
//        if (in_array($now->dayOfWeek, [0, 6])) {
//            return false;
//        }
//        return true;
//    }
//}

