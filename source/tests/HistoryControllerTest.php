<?php


declare(strict_types=1);

namespace Tests;

use Slim\Exception\HttpUnauthorizedException;

/**
 * Class HistoryControllerTest
 * @package Tests
 */
class HistoryControllerTest extends BaseTestCase
{
    /**
     * @var \Slim\App
     */
    protected $app;

    /**
     * @throws \Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->app = $this->getAppInstance();
    }

    public function testHistoryRouteIsProtectedByJwt()
    {
        $basic = 'Basic ' . base64_encode('user-success:user-success');
        // Arrange
        $header = ['HTTP_ACCEPT' => 'application/json', "Authorization" => $basic];
        $request = $this->createRequest('GET', '/history', $header);

        // Assert
        $this->expectException(HttpUnauthorizedException::class);

        // Act
        $response = $this->app->handle($request);
    }

    public function testHistoryRouteNoDataToShow()
    {
        // Arrange
        $user = [
            'name' => 'history-user',
            'username' => 'history-user',
            'email' => 'history-user@email.com',
            'password' => 'history-user'
        ];

        //Create new user with no history data
        $userRequest = $this->createRequest('POST', '/user/create', ['HTTP_ACCEPT' => 'application/json'], []);
        $userRequest = $userRequest->withParsedBody($user);
        $this->app->handle($userRequest);

        // Getting JWT token
        $basic = 'Basic ' . base64_encode('history-user:history-user');
        $headers = ['HTTP_ACCEPT' => 'application/json', 'Authorization' => $basic];
        $loginRequest = $this->createRequest('POST', '/user/login', $headers);

        // Login
        $loginResonse = $this->app->handle($loginRequest);
        $body = (string)$loginResonse->getBody();
        $arrBody = (array)json_decode($body);
        $headers = ['HTTP_ACCEPT' => 'application/json', 'Authorization' => "Bearer ".$arrBody['JWT']];

        // request history endpoint
        $historyRequest = $this->createRequest('GET', '/history',$headers);
        $historyResponse = $this->app->handle($historyRequest);
        $status = $historyResponse->getStatusCode();
        $message = trim((string)$historyResponse->getBody(),'"');

        // Assert
        $this->assertEquals(200, $status);
        $this->assertEquals('There is no history for logged user', $message);
    }

    public function testHistoryUserHasHistoryToShow()
    {
        // Getting JWT token
        $basic = 'Basic ' . base64_encode('history-user:history-user');
        $headers = ['HTTP_ACCEPT' => 'application/json', 'Authorization' => $basic];
        $loginRequest = $this->createRequest('POST', '/user/login', $headers);

        // Login
        $loginResponse = $this->app->handle($loginRequest);
        $body = (string)$loginResponse->getBody();
        $arrBody = (array)json_decode($body);
        $headers = ['HTTP_ACCEPT' => 'application/json', 'Authorization' => "Bearer ".$arrBody['JWT']];

        // Arrange
        $stockRequest = $this->createRequest('GET', "/stock",$headers);
        $stockRequest = $stockRequest->withQueryParams(["q" => 'AAPL.US']);
        $this->app->handle($stockRequest);

        // request history endpoint
        $historyRequest = $this->createRequest('GET', '/history',$headers);
        $historyResponse = $this->app->handle($historyRequest);
        $status = $historyResponse->getStatusCode();
        $message = json_decode((string)$historyResponse->getBody());

        // Assert
        $this->assertEquals(200, $status);
        $this->assertObjectHasAttribute('symbol', $message[0]);

    }

}