## Slim Framework Example
## Target

- User should be able to create user
- Login in the app
- Retrieve market data from stooq.com
- Send email of retrivied data
- Access your queries history

## Dependencies
- See [Docker](https://docs.docker.com/get-docker/) docs to Install

## Uses
- PHP
- Slim Framework
- MySQL
- Docker
- Composer
- JWT token
- PHP Mailer
- PHPUnit

## Running project

#### Before running docker, make sure you have the following ports available in your environment
```bash
  NGINX: 8000
  MYSQL: 3306
```

### Run Docker

- In the root directory with docker installed in your computer you should run the following command.
```bash
  docker-compose up -d --build
```
### Install composer dependencies
```bash
  docker-compose exec php composer install
```

### Create your .env file
```bash
  docker-compose exec php cp .env.example .env
```

### Configure your .env file

- You can fill with your own smtp.server and credentials, however an easy way is follow the steps below:
  - create a `Gmail` account
  - Access the Gmail [Security Page](https://myaccount.google.com/security)
  - Configure `Two Factor Auth` in security page
  - Create an `App Password` and save the given password

```bash
  MAILER_HOST=smtp.gmail.com
  MAILER_PORT=587
  MAILER_USERNAME=gmailacount@gmail.com
  MAILER_PASSWORD=given app password
  MAILER_SECURITY=tls
```

- According with the mysql image docker
```bash
  DB_DRIVER=mysql
  DB_HOST=mysql_slim
  DB_NAME=db_slim
  DB_USER=root
  DB_PASSWORD=root
```

- Set your JWT token
```bash
  JWT_TOKEN=exampleapp
```
### Run migrate scripts
```bash
  docker-compose exec php composer users-migrate
```
```bash
  docker-compose exec php composer histories-migrate
```

### Run tests
```bash
  docker-compose exec php composer test
```

# About API
- You can test your API Routes in an easy way installing [POSTMAN](https://www.postman.com/downloads/)
- Application prefix `http://localhost:8000`
- Routes
  - GET `/create/user`
  - POST `/user/login`
  - GET `/stock`
  - GET `/history`
   
### Routes
- type GET `/create/user`
  - Create an User passing requested params on `BODY` key => value
    ```bash
      name => John Doe
      username => jdoe
      email => john-doe@gmail.com
      password => johndoe123
    ```
  - Success
    - Route will return message `"User created with success"` with `201` status code
  - Errors
    - In case of invalid email `"Invalid Email"` with `400` status code
    - In case of user exists `"User already exists"` with `400` status code
    - In case of empty values `"Empty values"` with `400` status code
---
- type POST `/user/login`
  - Get a JWT Token to authorize the protected routes passing a `Basic auth` on `HEADER`
    - Use the [Postman Authorization tab](https://cdn.discordapp.com/attachments/260816154409304094/1060975787781668995/image.png) set `Basic Auth`
  - Success
    - Route will return JWT token with `200` status code
    ```json
      {
          "message": "User logged",
          "JWT": "token"
      }
    ```
  - Errors
    - If not match credentials will return the following JSON with `401` status code
    ```json
      {
          "message": "401 Unauthorized"
      }
    ```
---
- type GET `/stock`
  - This route needs a Bearer token which is obtained via `/user/login` route
    - Get a Stock Market value from stooq.com by receiving a `queryParams` as `q` and send an email with the Market data. 
    - Use [Postman Authorization tab](https://cdn.discordapp.com/attachments/260816154409304094/1060980996385820772/image.png)
  to set `Bearer Token`
  - Success 
    - Route will return a JSON with requested market data and `200` status code and send an [Email](https://cdn.discordapp.com/attachments/260816154409304094/1060983808117186610/image.png)
    ```json
      {
          "name": "APPLE",
          "symbol": "AAPL.US",
          "open": 127.13,
          "high": 127.77,
          "low": 124.76,
          "close": 125.02
      }
    ```
  - Errors
    - In case of empty or invalid `queryParams` as `q` will return message `"Unprocessable Entity"` with `422` status code
    - If route not found a stock data but `queryParams` as `q` is valid, the route will return message `"Nothing to show with the provided stock code"` with `404` status code
    - If JWT token not match will return the following JSON with `401` status code
    ```json
      {
          "message": "401 Unauthorized"
      }
    ```
---
- type GET `/history`
  - This route needs a Bearer token which is obtained via `/user/login` route
    - See `/stock` route informations about how to send a `Bearer Token`
  - Success
    - Route will return a JSON with history of user market data request and `200` status code
    ```json
      [
            {
                "date": "2023-01-06T18:34:57Z",
                "name": "APPLE",
                "symbol": "AAPL.US",
                "open": 126.01,
                "high": 128.48,
                "low": 124.89,
                "close": 128.12
            },
            {
                "date": "2023-01-06T14:28:55Z",
                "name": "APPLE",
                "symbol": "AAPL.US",
                "open": 127.13,
                "high": 127.77,
                "low": 124.76,
                "close": 125.02
            }
       ]
    ```
  - Errors
    - Is not an error exactly but will return message `"There is no history for logged user"` with `200` status code