<?php

declare(strict_types=1);

namespace App;

require_once __DIR__ . '/../app/bootstrap.php';

use App\Repositories\MailerRepository;
use Models\Users;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpUnauthorizedException;
use App\Repositories\StockRepository;

/**
 * StockController
 */
class StockController
{

    private $stockRepository;
    private $mailerRepository;

    public function __construct(StockRepository $stockRepository, MailerRepository $mailerRepository)
    {
        $this->stockRepository = $stockRepository;
        $this->mailerRepository = $mailerRepository;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @requires q (string)
     * @return Response
     */
    public function stock(Request $request, Response $response): Response
    {
        // Auth
        $logInfo = $request->getAttribute('log_info');
        $user = Users::find($logInfo['id']);
        if (empty($user)) {
            throw new HttpUnauthorizedException($request);
        }

        $info['status'] = 422;
        $info['message'] = "Unprocessable Entity";
        $stockSymbol = $request->getQueryParams()['q'];

        // validate request params
        if(strlen($stockSymbol) > 0 && is_numeric($stockSymbol) == false) {

            // call getStock method
            $stream = $this->stockRepository->getStock($request);
            $headers = str_getcsv($stream[0]);
            $values = str_getcsv($stream[1]);

            // validate if found something
            if($values[1] != "N/D") {
                // associante key => values from csv text
                for ($i = 0; $i < count($headers) ; $i++) {
                    $assoc[mb_strtolower($headers[$i])] = $values[$i];
                }

                // Insert History
                $info = $this->stockRepository->transaction($assoc, $logInfo['id']);
                if($info['bool_status'] == true) {
                    // Mount JSON response to user
                    $info['message'] = $this->stockRepository->json($assoc);
                    $info['status'] = 200;

                    // send email
                    $this->mailerRepository->send($logInfo['email'],$logInfo['name'],(string)json_encode($assoc), $info['message']['name']);
                }
            } else {
                $info['status'] = 404;
                $info['message'] = "Nothing to show with the provided stock code";
            }
        }

        $response->getBody()->write(json_encode($info['message'], JSON_PRETTY_PRINT));
        return $response->withStatus($info['status']);
    }





}