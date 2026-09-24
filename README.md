# Laravel Posts API

Laravel 13 REST API for managing posts and their authors.

## Requirements

- PHP 8.3 or newer with the PDO SQLite extension
- Composer 2
- SQLite 3

## Setup

```sh
composer install
php -r "file_exists('.env') || copy('.env.example', '.env');"
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate
```

## Run

```sh
php artisan serve
```

The API is available at `http://127.0.0.1:8000/api`.

## Endpoints

| Method | URI | Description |
| --- | --- | --- |
| GET | `/api/posts` | List posts, 15 per page |
| POST | `/api/posts` | Create a post |
| GET | `/api/posts/{post}` | Show a post |
| PUT/PATCH | `/api/posts/{post}` | Update a post |
| DELETE | `/api/posts/{post}` | Delete a post |

Create a post:

```sh
curl -X POST http://127.0.0.1:8000/api/posts \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{"user_id":1,"title":"Hello","body":"First post"}'
```

List posts:

```sh
curl -H 'Accept: application/json' http://127.0.0.1:8000/api/posts
```
