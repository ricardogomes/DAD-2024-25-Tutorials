# Changes to the DAD Project

This tutorial addresses some mandatory changes to our deployments, as well as some suggestions.

## Updates to the deployments

In order to keep the cluster running we implemented a few quotas and limits. They will be enforced directly by the cluster but we can help by defining the resources our deployments need.

Another implementation was a definition of priorities that tries to keep the pods that run Laravel and MySQl up. This is useful because both of these pods hold state and if they are the last to go in the case of resource shortage we don't need to run commands like `php artisan migrate`.

The changes are simple and they involve all our deployments:

- deployment\kubernetes-laravel.yml
- deployment\kubernetes-mysql.yml
- deployment\kubernetes-vue.yml
- deployment\kubernetes-ws.yml

The relevant code is:

```yml
priorityClassName: high-priority | low-priority
resources:
  requests:
    memory: "256Mi"
    cpu: "100m"
  limits:
    memory: "512Mi"
    cpu: "300m"
```

Bellow are the files (also present in the [repository](https://github.com/ricardogomes/DAD-2024-25-Tutorials/tree/main/code/deployment) ) that we need to change and the values for these properties.

::: danger
NOTE: we need to keep our group namespace, so either copy the relevant lines or replace the files and change the groups
:::

### Laravel

<<< ../code/deployment/kubernetes-laravel.yml

### MySQL

<<< ../code/deployment/kubernetes-mysql.yml

### Vue

<<< ../code/deployment/kubernetes-vue.yml

### WebSockets

<<< ../code/deployment/kubernetes-ws.yml

## Update to the Vue App - support for page refreshes

The way we deployed our projects in the intermediate submission didn't support page refreshes (besides the home page). This is because we are using Vue Router history mode, that keeps the navigation routes in the url, and the web server by default will try to find a file in the path we're trying to reach.

For example if we tried to refresh the /testers/laravel page, the web server would try to find a laravel.html file on the testers folder, which does not exist.

To solve this we need to instruct Nginx (our web server) to always point to the index.html at the base of the project, and let Vue Router handle the routing.

To do this we need to add a file called nginx.conf to our vue project folder with the following contents (file also in the repository):

<<< ../code/vue/nginx.conf

The most relevant line is `try_files $uri $uri/ /index.html;` that tells Nginx to redirect all requests that do not have a corresponding file to the `/index.html` file.

We also need to add a line to our `DockerfileVue` or simply replace it completely with:

<<< ../code/deployment/DockerfileVue

The relevant line is:

```docker
COPY nginx.conf /etc/nginx/conf.d/default.conf

```

## Handling Large Data

One issue that some students are having is the requests to historical game or transaction data. In a production environment were we can't be sure of the size of our data it's a bad practice to call for all the data at once. Even more so if we want to do filtering or sorting. In the case of our Kubernetes cluster if a request demands too much from a particular pod it needs to get rescheduled to a node that has more resources and given that we don't have multiple replicas of our pods this means the pod goes down.

The simplest way to handle this is to use pagination on the server. The testing application as been updated with an example of this feature for the games history.

You can see it in action at our deployment: http://web-teachers-172.22.21.101.sslip.io/testers/laravel. The table only appears after the login test,

And you can se the code at the [repository](https://github.com/ricardogomes/DAD-2024-25-Tutorials/tree/main/code/deployment), where the more relevant files are:

### GameController

<<< ../code/laravel/app/Http/Controllers/GameController.php

### Vue Game Pinia Store

<<< ../code/vue/src/stores/games.js

### Vue Laravel Tester Component

<<< ../code/vue/src/components/LaravelTester.vue
