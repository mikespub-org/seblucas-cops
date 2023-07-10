#!/bin/sh
#
# See https://docs.docker.com/build/building/multi-platform/
#
# docker run --privileged --rm tonistiigi/binfmt --install all
# docker buildx create --name mybuilder --driver docker-container --bootstrap --use
# docker buildx build --platform linux/amd64,linux/arm64,linux/arm/v7 -t <username>/<image>:latest --push .
# docker buildx imagetools inspect <username>/<image>:latest
docker buildx use mybuilder
docker compose -f docker-compose.yaml build --push
docker compose -f docker-compose-dev.yaml build --push
