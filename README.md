# Maximized Living Auth
This is the repo for the standalone Maximized Living Cognito login service.

### Setup
1. Create a copy of `.env.example` and rename it to `.env`. Configure any environment variables needed.
2. run `php artisan key:generate` to generate a new Laravel application key
3. run `composer install` to install required composer packages
4. run `npm install` to install required npm packages
5. run `npm run dev` to compile assets
6. run `php artisan serve` to run project using built in development server

### Built With
* [Laravel](https://laravel.com/)
* [Amazon AWS SDK for PHP](http://docs.aws.amazon.com/aws-sdk-php/v3/guide/index.html)
* [Amazon Cognito](https://aws.amazon.com/documentation/cognito/)
