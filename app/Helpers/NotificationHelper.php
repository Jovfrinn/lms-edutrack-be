<?php

use App\Models\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

if (!function_exists('sendNotification')) {
    function sendNotification($uId, $eId, $title, $message,)
    {
        $n = new Notification();
        $n->user_id = $uId;
        $n->employee_id = $eId;
        $n->title = $title;
        $n->message = $message;
        $n->is_read = 0;
        $n->save();
    }

    if (! function_exists('getNotification')) {
        function getNotification()
            {
                return Notification::with('employee')->where('user_id', Auth::user()->id)->orderBy('created_at', 'desc')->get();
            }
    }
if (! function_exists('countNotification')) {
    function countNotification()
    {
        return Notification::where('user_id', Auth::user()->id)->where('is_read', 0)->count();
    }
}

if (!function_exists('timeAgo')) {
    function timeAgo($datetime)
    {
        $carbon = $datetime instanceof Carbon ? $datetime : Carbon::parse($datetime);
        return $carbon->diffForHumans();
    }
}
}
