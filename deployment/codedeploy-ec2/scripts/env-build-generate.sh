#!/usr/bin/env bash

environment=`env | grep CODEBUILD_BUILD_ID | cut -c 20-22`

rm -f .env*

aws ssm get-parameters-by-path \
  --region ap-southeast-1 \
  --path "/${environment}/ec2-web-build" \
  --with-decryption | \
  jq -r '.Parameters | map([.Name[19:], .Value] | join("=")) | join("\n")' | \
  tee .env > /dev/null 2>&1
