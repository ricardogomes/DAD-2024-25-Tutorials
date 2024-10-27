# Docker

Containers are an approach that allows us to isolate software environments without the need for full virtualization.

Docker is the must well known container solution, but it's not the only one.

## Use Cases

There are several use cases for containers, but for our purposes we can focus on just two:

- Development Environment creation and management
- Software Deployment

### Development Environments

Containers allow us to define a set of dependencies for our developement environments and make sure they are available no matter where we are using them.

We can develop a Laravel application without the need to install a specific PHP version on our machine. We can even have two Laravel projects with different versions (and diferent PHP dependencies) and they will not conflict with each other.

### Deployment

Since containers allow us to isolate software dependencies they are a great option for software deployment, because we can define exactly what we need to have the software running and not have to worry about it conflicting with other applications deployed to the same server.

## Concepts

This tutorial is not an in-deept view on containers or Docker, but we should still have some common vocabulary so here are the most relevant concepts.

### Images, Containers, Networks and Volumes

An image is a static build of a set of depencies. For instance, in a Laravel project we might use a PHP image that has PHP version 8.1 and a set of PHP extensions needed for Laravel.

A container is a running image. Again in a Laravel project we could use the PHP 8.1 image to boot up a container that gets our code and runs it.

A network is the same concept we already know, the important part here is that Docker (and other container engines) can operate on a separate network to further isolate our environments. For us this means that we need to define ways of _exposing_ the endpoints we want to connect to from our own network.

Finally a volume is the primary way that we use to persist data. By design containers are ephemeral, meaning that they are short lived and can be disposed of because we can just re-build the same thing at any time. This means that we need a way to persist data that is not present in the images. This also allows us to _map_ our own storage as a volume in a container, thus allowing us the read/write to our hard drive and have those changes reflect on the container.

### Dockerfile and Docker Hub

A Dockerfile is where we describe how to build an image. It has its own syntax, but essentially its a set of commands that install dependencies and define configurations.

This is the Dockerfile for Laravel that uses PHP 8.3:

<<< ./assets/Dockerfile

The images used on a Dockerfile must be accessible in some way. In the case of the previous Dockerfile it uses hte `ubuntu:22.04` image (we can see this in the first line `FROM ubuntu:22.04`).By default Docker will connect to [Docker Hub](https://hub.docker.com/), a public container registry to download those images.

### Docker Compose

Docker Compose is a way for defining a set of containers that work together and their configurations. This allows us to set up several containers for more complex environments. The simples example, and the won we will be using, is to have an application server (PHP in our case) and a database server (MySQL in our case).

This is the docker-compose.yaml file present in a Laravel instance using Sail.

<<< ../code/laravel/docker-compose.yml

As we can see it describes two services, a Laravel application server and a MySQL server.

## Commands

There are several ways of interacting with containers using Docker, these are some of the most commonly used commands:

```bash

docker ps # shows the currently active containers

docker ps -a # shows active and inactive containers

docker images # shows a list of local images (downloaded from a registry or built locally)

docker image prune # deletes images that are not used by a container

docker system prune # deletes docker resources (images, stopped containers, volumes) that are not in use


docker-compose up # starts a set of containers based on a docker-compose file present in the current directory

docker-compose up -d # same as previous but in a detached form, meaning the console we still be available.

sail up
sail up -d
# same as docker-compose but wit some added benefits for Laravel Sail

```

## Additional Resources

This video is a full overview of Docker and containers.

<iframe width="700" height="400" src="https://www.youtube.com/embed/pg19Z8LL06w?si=mo6ajA116oKLI-s3" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>

:::tip Adicional Info

- [Dockerfile reference | Docker Docs](https://docs.docker.com/reference/dockerfile/)
- [Docker Compose | Docker Docs](https://docs.docker.com/compose/)
  :::
