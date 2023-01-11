<?php

declare(strict_types=1);

namespace App;

require_once __DIR__ . '/../app/bootstrap.php';

use App\Repositories\HistoryRepository;
use Models\Users;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpUnauthorizedException;

/**
 * HistoryController
 */
class HistoryController
{
    private $historyRepository;

    public function __construct(HistoryRepository $historyRepository)
    {
        $this->historyRepository = $historyRepository;
    }

    public function history(Request $request, Response $response): Response
    {
        // Auth
        $logInfo = $request->getAttribute('log_info');
        $user = Users::find($logInfo['id']);
        if (empty($user)) {
            throw new HttpUnauthorizedException($request);
        }

        $info = $this->historyRepository->getHistory($logInfo['id']);

        $response->getBody()->write($info);
        return $response->withStatus(200);
    }

}