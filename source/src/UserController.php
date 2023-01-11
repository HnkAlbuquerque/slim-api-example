<?php

declare(strict_types=1);

namespace App;

require_once __DIR__ . '/../app/bootstrap.php';

use App\Repositories\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * UserController
 */
class UserController
{

    private $userRepository;

    /**
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function create(Request $request, Response $response): Response
    {
        // Call validate function from UserRepository
        $info = $this->userRepository->requestValidate($request);

        // Call db function to insert user from UserRepository
        $info = $this->userRepository->transaction($info, $request);

        $response->getBody()->write(json_encode($info['message'], JSON_PRETTY_PRINT));
        return $response->withStatus($info['status']);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function login(Request $request, Response $response): Response
    {
        $info = $this->userRepository->getJwtToken($request);

        $response->getBody()->write($info['body']);
        return $response->withStatus($info['status']);
    }



}