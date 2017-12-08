<?php
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

