group "default" {
  targets = ["frankenphp"]
}

target "frankenphp" {
  context = "."
  dockerfile = "docker/octane/FrankenPHP.Dockerfile"
  tags = ["yinyang/frankenphp:php8.4"]
  cache-from = ["type=local,src=/tmp/docker-cache"]
  cache-to = ["type=local,dest=/tmp/docker-cache,mode=max"]
}
