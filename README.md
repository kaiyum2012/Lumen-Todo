# Todo App (API) - Lumen

[![Build Status](https://travis-ci.org/laravel/lumen-framework.svg)](https://travis-ci.org/laravel/lumen-framework)
[![Total Downloads](https://img.shields.io/packagist/dt/laravel/framework)](https://packagist.org/packages/laravel/lumen-framework)
[![Latest Stable Version](https://img.shields.io/packagist/v/laravel/framework)](https://packagist.org/packages/laravel/lumen-framework)
[![License](https://img.shields.io/packagist/l/laravel/framework)](https://packagist.org/packages/laravel/lumen-framework)

## Technology Stack

- PHP >=7.3
- Lumen 8
- MySql >=5.6/ MariaDB >=10
- NPM

## Development

### Setup

Clone repo to local directory with the command `git clone https://github.com/kaiyum2012/Lumen-Todo.git`

1. Create a `.env` based on the example in the repo with the command `cp .env.example .env`
2. Adjust values in the .env to match the environment the application will be working in. See below for the important
   values to change.
    - **APP_URL**: Set this to the URL the application will be accessed from.
    - **APP_KEY**: Set 32 char key.
    - **DB_HOST**: Set to the hostname or IP address of the MySql/Maria database server
    - **DB_DATABASE**: Name of the database the application is using
    - **DB_USERNAME**: Database username
    - **DB_PASSWORD**: Database password
3. install dependency `composer install`
4. You should now be able to access the application in your browser if the DNS was set up properly. In the case of a
   local deployment, go to [http://localhost:8000](http://localhost)

## Testing

#### Unit Test

1. create testing database `todos_test`
2. test command ` .\vendor\bin\phpunit`

#### Api test

1. download postman collection json file from `'\Api Schema'`.
2. set global variables: `url=http://localhost:8000/api` and `token={AUTH_TOKEN}` // {AUTH_TOKEN} = A token received
   upon signup or login.

3. Import into Postman and start testing

## License

The Lumen framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
