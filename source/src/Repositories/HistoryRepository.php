<?php

namespace App\Repositories;

use Firebase\JWT\JWT;
use Slim\Exception\HttpUnauthorizedException;
use Models\Histories;

class HistoryRepository
{
    public function getHistory($id): String
    {

        $histories = Histories::where('user_id',$id)->get()->sortByDesc('created_at');

        $info['message'] = "There is no history for logged user";

        $arr = array();
        if(count($histories) > 0) {
            foreach ($histories as $history) {
                $obj = new \stdClass();
                $obj->date = $history->created_at->format('Y-m-d\TH:i:s\Z');
                $obj->name = $history->name;
                $obj->symbol = $history->symbol;
                $obj->open = (double)$history->open;
                $obj->high = (double)$history->high;
                $obj->low = (double)$history->low;
                $obj->close = (double)$history->close;
                array_push($arr, $obj);
            }
            return json_encode($arr, JSON_PRETTY_PRINT);
        }

        return json_encode($info['message'], JSON_PRETTY_PRINT);
    }
}
