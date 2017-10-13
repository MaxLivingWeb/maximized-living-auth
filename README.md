# Maximized Living Auth
This is the repo for the standalone Maximized Living Cognito login service.

### Requirements
PHP must be compiled with GMP support. [Instructions](http://php.net/manual/en/gmp.installation.php)

### Setup
1. Create a copy of `.env.example` and rename it to `.env`. Configure any environment variables needed.
> 1. `AWS_ACCESS_KEY_ID`. The access key of a [AWS IAM user](https://console.aws.amazon.com/iam/home)
> 2. `AWS_SECRET_ACCESS_KEY`. The access key secret of a [AWS IAM user](https://console.aws.amazon.com/iam/home)
> 3. `AWS_COGNITO_USER_POOL_ID`. The [user pool](https://us-east-2.console.aws.amazon.com/cognito/users/) ID 
> 4. `AWS_COGNITO_APP_CLIENT_ID`. App client ID. Must be a client of the user pool ID 
> 5. `AWS_COGNITO_APP_CLIENT_SECRET`. App client secret.
2. run `php artisan key:generate` to generate a new Laravel application key
3. run `composer install` to install required composer packages
4. run `npm install` to install required npm packages
5. run `npm run dev` to compile assets
6. run `php artisan serve` to run project using built in development server

### Built With
* [Laravel](https://laravel.com/)
* [Amazon AWS SDK for PHP](http://docs.aws.amazon.com/aws-sdk-php/v3/guide/index.html)
* [Amazon Cognito](https://aws.amazon.com/documentation/cognito/)
    
### Documentation
* [SRP](http://srp.stanford.edu/)
    * [AWS High-level Javascript SDK](https://github.com/aws/amazon-cognito-identity-js/tree/master/src). Example of SRP algorithms.
    * [SRP Protocol Design](http://srp.stanford.edu/design.html)
    * [Wikipedia](https://en.wikipedia.org/wiki/Secure_Remote_Password_protocol)