## Laravel Create User & Get User APIs 
Basic Laravel usersAPIs.

----

### Language & Framework Used:
1. PHP >= 8.x
1. Laravel 11.x

### Architecture Used:
1. Laravel 11.x
1. Model Based Eloquent Query

### API List:
##### Authentication Module
1. [x] Create User API 
1. [x] List of Users (with and without token)
1. [x] Login API with Token
1. [x] Logout

### How to Run:
1. Clone Project - 

1. Go to the project drectory by `cd checkproof` & Run the
2. Create `.env` file & Copy `.env.example` file to `.env` file
3. Create a database called - `db_checkproof`.
4. Install composer packages - `composer install`.
5. Now migrate and seed database to complete whole project setup by running this-
``` bash
php artisan migrate:refresh
php artisan db:seed
```
It will create `11` Users (1 admin, and random no. of managers & users) and `10` Dummy Orders.

6. Run the server -
``` bash
php artisan serve
```
8. Open Postman -
GET API - Users List:
http://127.0.0.1:8000/api/users 
optional query params:
search={string to search in name or email}
sort_by={optional values: name/email/created_at'} - default created_at
sort_order={optional values: asc,desc} - default asc

POST API: Create User:
http://127.0.0.1:8000/api/users
Sample data for body:
{
    "email": "mini@gmail.com",
    "name": "Mini",
    "password": "12345678"
}

POST API: Login:

Sample data: 
{
    "email": "mini@gmail.com",
     "password": "12345678"
}
Sample response:
{
    "user": {
        "id": 23,
        "name": "Mini",
        "email": "mini@gmail.com",
        "email_verified_at": null,
        "role": "user",
        "active": 1,
        "created_at": "2025-01-05T19:58:12.000000Z",
        "updated_at": "2025-01-05T19:58:12.000000Z"
    },
    "token": "12|NdwkTLIoRbg8UDC6WddVWuKBKyJ3gdymdxc1haAN12d5d3c6"
}
Value of "token" can be used in Bearer Authentication for Get Users API to affect 'can_edit' feature.

### Procedure
1. First Login with the given credential or any other user credential
1. Set bearer token to Post Header as Bearer Authentication
1. Hit Any API, You can also hit any API, before authorization header data set to see the effects.

