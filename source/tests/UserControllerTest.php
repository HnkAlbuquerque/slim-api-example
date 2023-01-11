<?php


declare(strict_types=1);

namespace Tests;

use Models\Users;
use Slim\Exception\HttpUnauthorizedException;

/**
 * Class HelloTest
 * @package Tests
 */
class UserControllerTest extends BaseTestCase
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

    public function testUserCreateRouteCreateUserWithSuccess()
    {
        // Arrange
        $user = [
            'name' => 'user-success',
            'username' => 'user-success',
            'email' => 'user-success@email.com',
            'password' => 'user-success'
        ];
        $request = $this->createRequest('POST', '/user/create', ['HTTP_ACCEPT' => 'application/json'], []);
        $request = $request->withParsedBody($user);

        // Act
        $response = $this->app->handle($request);
        $status = $response->getStatusCode();
        $message = trim((string)$response->getBody(),'"');

        // Assert
        $this->assertEquals(201, $status);
        $this->assertEquals("User created with success", $message);
    }

    public function testUserCreateRouteCreateUserEmailValidate()
    {
        // Arrange
        $user = [
            'name' => 'no-email',
            'username' => 'no-email',
            'email' => 'no-email',
            'password' => 'no-email'
        ];
        $request = $this->createRequest('POST', '/user/create', ['HTTP_ACCEPT' => 'application/json'], []);
        $request = $request->withParsedBody($user);

        // Act
        $response = $this->app->handle($request);
        $status = $response->getStatusCode();
        $message = trim((string)$response->getBody(),'"');

        // Assert
        $this->assertEquals(400, $status);
        $this->assertEquals('Invalid email', $message);
    }

    public function testUserCreateRouteCreateUserEmptyValuesValidate()
    {
        // Arrange
        $user = [
            'name' => 'user-test',
            'username' => '',
            'email' => '',
            'password' => ''
        ];
        $request = $this->createRequest('POST', '/user/create', ['HTTP_ACCEPT' => 'application/json'], []);
        $request = $request->withParsedBody($user);

        // Act
        $response = $this->app->handle($request);
        $status = $response->getStatusCode();
        $message = trim((string)$response->getBody(),'"');

        // Assert
        $this->assertEquals(400, $status);
        $this->assertEquals('Empty values', $message);
    }

    public function testUserCreateRouteCreateUserAlredyExistsValidate()
    {
        // Arrange
        $user = [
            'name' => 'user-success',
            'username' => 'user-success',
            'email' => 'user-success@email@com',
            'password' => 'user-success'
        ];
        $request = $this->createRequest('POST', '/user/create', ['HTTP_ACCEPT' => 'application/json'], []);
        $request = $request->withParsedBody($user);

        // Act
        $response = $this->app->handle($request);
        $status = $response->getStatusCode();
        $message = trim((string)$response->getBody(),'"');

        // Assert
        $this->assertEquals(400, $status);
        $this->assertEquals('User already exists', $message);
    }

    public function testUserUnauthorizedWhenGetJwtToken()
    {
        $basic = 'Basic ' . base64_encode('error-user:error-pass');
        // Arrange
        $header = ['HTTP_ACCEPT' => 'application/json', "Authorization" => $basic];
        $request = $this->createRequest('POST', '/user/login', $header);

        // Assert
        $this->expectException(HttpUnauthorizedException::class);

        // Act
        $response = $this->app->handle($request);
    }

    public function testUserGetJwtTokenWithSuccess()
    {
        $basic = 'Basic ' . base64_encode('user-success:user-success');
        // Arrange
        $header = ['HTTP_ACCEPT' => 'application/json', "Authorization" => $basic];
        $request = $this->createRequest('POST', '/user/login', $header);

        // Assert
        $response = $this->app->handle($request);
        $objBody = json_decode((string)$response->getBody());

        // Act
        $this->assertArrayHasKey('JWT', (array)$objBody);
    }
}