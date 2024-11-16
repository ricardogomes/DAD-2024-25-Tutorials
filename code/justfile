
GROUP := "dad-group-x"
VERSION := "1.0.0"


kubectl-pods:
    kubectl get pods

kubectl-apply:
    kubectl apply -f deployment

kubectl-update:
    kubectl delete -f deployment
    kubectl apply -f deployment


laravel-build group=GROUP version=VERSION:
    docker build -t registry.172.22.21.107.sslip.io/{{group}}/api:v{{version}} \
    -f ./deployment/DockerfileLaravel ./laravel \
    --build-arg GROUP={{group}} --debug
laravel-push group=GROUP version=VERSION:
    docker push registry.172.22.21.107.sslip.io/{{group}}/api:v{{version}}

vue-build group=GROUP version=VERSION:
    docker build -t registry.172.22.21.107.sslip.io/{{group}}/web:v{{version}} -f ./deployment/DockerfileVue ./vue
vue-push group=GROUP version=VERSION:
    docker push registry.172.22.21.107.sslip.io/{{group}}/web:v{{version}}

ws-build group=GROUP version=VERSION:
    docker build -t registry.172.22.21.107.sslip.io/{{group}}/ws:v{{version}} -f ./deployment/DockerfileWS ./websockets

ws-push group=GROUP version=VERSION:
    docker push registry.172.22.21.107.sslip.io/{{group}}/ws:v{{version}}