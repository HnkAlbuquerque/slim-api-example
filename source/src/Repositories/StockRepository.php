<?php

namespace App\Repositories;

use Firebase\JWT\JWT;
use Models\Histories;
use Slim\Exception\HttpException;
use Slim\Exception\HttpUnauthorizedException;
use GuzzleHttp\Psr7;

class StockRepository
{

    public function getStock($request): array
    {

        $stock = $request->getQueryParams()['q'];
        $url = "https://stooq.com/q/l/?s=$stock&f=sd2t2ohlcvn&h&e=csv";
        $resource = Psr7\Utils::tryFopen($url, 'r');
        $stream = Psr7\Utils::streamFor($resource);
        $stream = explode(PHP_EOL, $stream->getContents());

        return $stream;
    }

    public function transaction($array, $id): array
    {

        $history = new Histories();
        $history->name      = $array['name'];
        $history->date      = $array['date'];
        $history->symbol    = $array['symbol'];
        $history->open      = $array['open'];
        $history->high      = $array['high'];
        $history->low       = $array['low'];
        $history->close     = $array['close'];
        $history->user_id   = $id;

        try {
            if ($history->save()) {
                $info["bool_status"] = true;
            }
        } catch (HttpException $e){
            $info['message'] = $e->getMessage();
        }

        return $info;
    }

    public function json($array): array
    {
        $responseArray = [
            "name" => $array['name'],
            "symbol" => $array['symbol'],
            "open" => (double)$array['open'],
            "high" => (double)$array['high'],
            "low" => (double)$array['low'],
            "close" => (double)$array['close']
        ];

        return $responseArray;
    }

}