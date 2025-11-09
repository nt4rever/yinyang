group "default" {
  targets = ["php-fpm"]
}

target "php-fpm" {
  context = "."
  dockerfile = "docker/php-fpm/Dockerfile"
  tags = ["yinyang/php-fpm:php8.4"]
  cache-from = ["type=local,src=/tmp/docker-cache"]
  cache-to = ["type=local,dest=/tmp/docker-cache,mode=max"]
}
