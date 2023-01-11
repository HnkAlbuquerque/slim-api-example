<?php


declare(strict_types=1);

namespace Tests;

use Slim\Exception\HttpUnauthorizedException;

/**
 * Class StockControllerTest
 * @package Tests
 */
class StockControllerTest extends BaseTestCase
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

    public function testStockRouteIsProtectedByJwt()
    {
        $basic = 'Basic ' . base64_encode('user-success:user-success');
        // Arrange
        $header = ['HTTP_ACCEPT' => 'application/json', "Authorization" => $basic];
        $request = $this->createRequest('GET', '/stock', $header);

        // Assert
        $this->expectException(HttpUnauthorizedException::class);

        // Act
        $response = $this->app->handle($request);
    }

    public function testStockUnprocessableEntityError()
    {
        // Arrange
        $user = [
            'name' => 'stock-user',
            'username' => 'stock-user',
            'email' => 'stock-user@email.com',
            'password' => 'stock-user'
        ];

        //Create new user with no history data
        $userRequest = $this->createRequest('POST', '/user/create', ['HTTP_ACCEPT' => 'application/json'], []);
        $userRequest = $userRequest->withParsedBody($user);
        $this->app->handle($userRequest);

        // Getting JWT token
        $basic = 'Basic ' . base64_encode('stock-user:stock-user');
        $headers = ['HTTP_ACCEPT' => 'application/json', 'Authorization' => $basic];
        $loginRequest = $this->createRequest('POST', '/user/login', $headers);

        // Login
        $loginResonse = $this->app->handle($loginRequest);
        $body = (string)$loginResonse->getBody();
        $arrBody = (array)json_decode($body);
        $headers = ['HTTP_ACCEPT' => 'application/json', 'Authorization' => "Bearer ".$arrBody['JWT']];

        // Arrange
        $stockRequest = $this->createRequest('GET', "/stock",$headers);
        $stockRequest = $stockRequest->withQueryParams(["q" => '434343']);

        $StockResonse = $this->app->handle($stockRequest);
        $status = $StockResonse->getStatusCode();
        $message = trim((string)$StockResonse->getBody(),'"');

        // Assert
        $this->assertEquals(422, $status);
        $this->assertEquals('Unprocessable Entity', $message);
    }

    public function testStockNotFoundInformationForStockCode()
    {
        // Getting JWT token
        $basic = 'Basic ' . base64_encode('stock-user:stock-user');
        $headers = ['HTTP_ACCEPT' => 'application/json', 'Authorization' => $basic];
        $loginRequest = $this->createRequest('POST', '/user/login', $headers);

        // Login
        $loginResonse = $this->app->handle($loginRequest);
        $body = (string)$loginResonse->getBody();
        $arrBody = (array)json_decode($body);
        $headers = ['HTTP_ACCEPT' => 'application/json', 'Authorization' => "Bearer ".$arrBody['JWT']];

        // Arrange
        $stockRequest = $this->createRequest('GET', "/stock",$headers);
        $stockRequest = $stockRequest->withQueryParams(["q" => 'ERRORAS.US']);

        $StockResonse = $this->app->handle($stockRequest);
        $status = $StockResonse->getStatusCode();
        $message = trim((string)$StockResonse->getBody(),'"');

        // Assert
        $this->assertEquals(404, $status);
        $this->assertEquals('Nothing to show with the provided stock code', $message);
    }

    public function testStockFoundInformationsWithSuccess()
    {
        // Getting JWT token
        $basic = 'Basic ' . base64_encode('stock-user:stock-user');
        $headers = ['HTTP_ACCEPT' => 'application/json', 'Authorization' => $basic];
        $loginRequest = $this->createRequest('POST', '/user/login', $headers);

        // Login
        $loginResonse = $this->app->handle($loginRequest);
        $body = (string)$loginResonse->getBody();
        $arrBody = (array)json_decode($body);
        $headers = ['HTTP_ACCEPT' => 'application/json', 'Authorization' => "Bearer ".$arrBody['JWT']];

        // Arrange
        $stockRequest = $this->createRequest('GET', "/stock",$headers);
        $stockRequest = $stockRequest->withQueryParams(["q" => 'AAPL.US']);
        $stockResponse = $this->app->handle($stockRequest);

        $status = $stockResponse->getStatusCode();
        $message = json_decode((string)$stockResponse->getBody());

        $this->assertObjectHasAttribute('symbol', $message);
        $this->assertEquals(200, $status);
    }

}