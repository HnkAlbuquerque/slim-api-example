<?php

namespace App\Repositories;

use Firebase\JWT\JWT;
use Models\Users;
use Slim\Exception\HttpUnauthorizedException;

class UserRepository
{
    public function requestValidate($request): array
    {

        $info['bool_status'] = true;
        $name = $request->getParsedBody()['name'];
        $username = $request->getParsedBody()['username'];
        $email = $request->getParsedBody()['email'];

        // Query
        $user = Users::where('username',$username)->orWhere('email',$email)->first();

        // Validating inputs
        if (!empty($user)) {
            $info['bool_status'] = false;
            $info['status'] = 400;
            $info['message'] = 'User already exists';
        }else if (empty($name) || empty($username) || empty($email)) {
            $info['bool_status'] = false;
            $info['status'] = 400;
            $info['message'] = 'Empty values';
        }else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $info['bool_status'] = false;
            $info['status'] = 400;
            $info['message'] = 'Invalid email';
        }

        return $info;
    }

    public function transaction($info, $request): array
    {

        if ($info['bool_status'] == true) {
            $user = new Users();
            $user->name = $request->getParsedBody()['name'];
            $user->username = $request->getParsedBody()['username'];
            $user->email = $request->getParsedBody()['email'];
            $user->password = password_hash($request->getParsedBody()['password'], PASSWORD_DEFAULT);

            try {
                if ($user->save()) {
                    $info['status'] = 201;
                    $info['message'] = 'User created with success';
                }
            } catch (Exception $e) {
                $info['message'] = $e->getMessage();
            }
        }

        return $info;
    }

    public function getJwtToken($request): array
    {
        // Basic
        $authorization = $request->getHeader('Authorization');
        $authorization = preg_replace('/Basic[\s]+/i', '', $authorization);
        $authorization = base64_decode($authorization[0]);

        $explodedAuth = explode(':', $authorization);

        // Search User
        $user = Users::where('username',$explodedAuth[0])->first();

        if (!empty($user)) {
            if (password_verify($explodedAuth[1], $user->password)) {
                $info['status'] = 200;
                $responseArray = array();
                $responseArray['message'] = 'User logged';
                $responseArray['JWT'] = JWT::encode([
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email
                ], $_ENV["JWT_TOKEN"], "HS256");

                $info['body'] = json_encode($responseArray, JSON_PRETTY_PRINT);

            }else {
                throw new HttpUnauthorizedException($request);
            }
        }else{
            throw new HttpUnauthorizedException($request);
        }

        return $info;
    }

}