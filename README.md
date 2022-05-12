
# Leaderboard API

Karma user ranking
API to be used by front end and mobile developers. Each user in our database has a karma
score, the higher the karma score they have, the better ranking position they get.

## Requirements
    - php : ^7.3|^8.0
    - MySQL
    - Redis

## DB Schema
users (
`id`: Primary key
`username`: Unique
`karma_score`: positive integer, default is 0.
`image_id`: foreign key.
)

images (
`id`: primary key
`url`: string
)


## Run Locally

Clone the project

```bash
  git clone https://gitlab.com/haidarjbeily1/karma-ranking-api.git
```

Go to the project directory

```bash
  cd karma-ranking-api
```

Install dependencies

```bash
  composer install
```

Copy .env.example to .env and write the environment variables

```bash
  cp .env.example .env
```

Generate application key

```bash
  php artisan key:generate
```
Run the migrations

```bash
  php artisan migrate
```

Seed the database

```bash
  php artisan db:seed
```

Cache the database in the Redis 

```bash
  php artisan redis:fill
```

Start the server

```bash
  php artisan serve
```


## API Reference

##### Get get the overall user position compared to all users depending on the karmascore, in addition to the 2 users right before him and the 2 users right after him. 
There are three versions of the API:
####
V1: I developed this API with the help of indexing in database so when querying the users the query will be optimized to O(n * log(n)) + database connection time 
####
```http
  GET /api/v1/user/${id}/karma-position
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `id` | `integer` | **Required**. represents the user id  |
| `limit` | `integer` |  represents the number of users objects needs to be returned. Default value => 5  |

####
V2: I enhanced the V1 API with the help of caching all users in the file with the help of file caching in laravel. I cached all the users objects and every 30 seconds I re-cache all users so when querying the users the query will be optimized by erasing the call database time  
####

```http
  GET /api/v2/user/${id}/karma-position
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `id` | `integer` | **Required**. represents the user id  |


####
V3: I developed this version with the help of REDIS. I added a command *php artisan redis:fill* to add all users to redis cache memory. The benefits of this version are the following: much faster when querying the leaderboard (*ordered set data structure in redis*) and the write operations can be more flexible and faster.
####

```http
  GET /api/v3/user/${id}/karma-position
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `id` | `integer` | **Required**. represents the user id  |
| `limit` | `integer` |  represents the number of users objects needs to be returned. Default value => 5  |




## Running Tests

To run tests, run the following command

```bash
  php artisan test
```


## Authors

- [@haidarjbeily](https://gitlab.com/HaidarJbeily)



