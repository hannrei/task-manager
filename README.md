# Task Manager API

## Description
This project is a simple Task Manager API. It is a project to apply my [Laravel](https://laravel.com/) knowledge and to demonstrate my skills in this framework.

This Task Manager API allows you to register yourself as a user. After the verification of your email, you can create, update, delete, and list your tasks. The listing of tasks can be filtered by, e.g., status, and you can order them by, e.g., due date. You can not only create tasks for yourself but also attach them to other users.

## Requirements
- [PHP 8.2.10](https://www.php.net/)
- [Composer 2.5.8](https://getcomposer.org/)
- [MySQL 8.0.34](https://www.mysql.com/) or another [DBMS supported by Laravel](https://laravel.com/docs/10.x/database)
## Installation
First, clone the repository. Then create a *.env* file. You can use the *.env.example* file as a template. 
To install the dependencies, run 
`composer install`.
Run 
`php artisan key:generate`
and 
`php artisan jwt:secret` 
to generate an application key and a [JWT](https://jwt.io/) secret key. 
You have to set the database connection. After that, you can run 
`php artisan migrate` 
to create the database tables. If you want to seed the database with some test data, you can run 
`php artisan db:seed`. 
All test users have the password *password*. To run the application, you can use 
`php artisan serve`.

## API Documentation
After you have started the application, you can access the API documentation at http://localhost:8000/api/documentation. It was created with [Swagger](https://swagger.io/).

## Use
You can either use the API documentation or use a tool like [Postman](https://www.postman.com/) to test the API.

If you don't want to use a test user, create your own user. Then you have to verify your email address. You can do this by clicking on the link in the email you received. If you have not changed the Mail settings in the .env file, you can find the email in the *storage/logs/laravel.log* file.

Now you can log in with your email address and your password. You will receive a JSON Web Token that you can use for authentication.

To test the API as an admin user, you can set the `NEW_USER_IS_ADMIN` variable in the *.env* file to `true`. Then every new user will be an admin user.

## License
[MIT](https://github.com/hannrei/task-manager/blob/main/LICENSE)
