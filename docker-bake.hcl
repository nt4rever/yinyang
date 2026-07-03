group "default" {
  targets = ["frankenphp"]
}

target "frankenphp" {
  context = "."
  dockerfile = "docker/development/FrankenPHP.Dockerfile"
  tags = ["yinyang/frankenphp:php8.4"]
}
