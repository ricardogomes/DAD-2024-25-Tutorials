# Laravel Sail

Laravel Sail is a wrapper over Docker and Docker Compose geared towards Laravel projects.

We can bootstrap a Laravel project using Sail by running the following command:

```bash
curl -s https://laravel.build/<name_of_project>?with=<comma separated list of dependencies> | bash

# example with laravel as the project (folder) name and o0nly MySQL as a dependency
curl -s https://laravel.build/laravel?with=mysql | bash
```

## Main Features

For our purposes the main features of Laravel Sail is to interact with our containers in the context of Laravel development.

During our development effort we need to use Laravel's `artisan` command to create resources or interact with our database. Without Sail we would need to push the necessary commands to the proper container. Sail allow us to do this is a simpler way.

Where are some examples of sail commands:

```bash

# create a controller via artisan
sail artisan make:controller TaskController --api
# equivalent docker command
docker exec -d laravel.test php artisan make:controller TaskController --api


# boot up a set of services (from docker-compose.yml)
sail up
# the same but in detached mode (no output on the console and the console is freed)
sail up -d

# shutdown a set of services
sail down

# access the MySQL database of a Laravel project via the MySQL client CLI
sail mysql

# access a shell and the PHP container
sail shell

# installing a PHP dependency via composer.json (inside the container)
sail composer require laravel/sanctum
```

## Special Case - Bootstrap a Sail project

We normally bootstrap a Laravel project that uses sail via the `curl -s https://laravel.build` command, but in some case we need to bootstrap a project with existing code (on our project we will need this because only one element of a group will create the initial instance).

One option would be to install PHP and Composer locally, and do a `composer install`. This would provide sail in the `vendor/bin/sail` path, but this would defeat the purpose of using Docker.

Another (the **recommended**) way is to use Docker for this by running this command:

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs
```

## Additional Resources

This video is an overview of Laravel Sail.

<iframe width="700" height="400" src="https://www.youtube.com/embed/S_z03NUUiBk?si=PmotDjPFIKKgi-kK" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>

:::tip Adicional Info

- [Laravel Sail - Laravel 11.x - The PHP Framework For Web Artisans](https://laravel.com/docs/11.x/sail)
- [laravel/sail: Docker files for running a basic Laravel application.](https://github.com/laravel/sail)
  :::
