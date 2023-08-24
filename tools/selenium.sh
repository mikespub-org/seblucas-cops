#!/bin/sh
#
# See https://hub.docker.com/r/selenium/standalone-chrome
#
# Add host=host.docker.internal here so that the container can access the local webserver
#
# This setup assumes that you have a local PHP webserver that serves COPS on
# the same system (*) as the docker host where the selenium container will run.
#
# (*) for development this can be a default WSL2 Linux server on your Win laptop ;-)
#
#docker run --rm -d -p 4444:4444 -p 7900:7900 --shm-size="2g" selenium/standalone-chrome:latest
#docker run --rm -d --network=host --shm-size="2g" selenium/standalone-chrome:latest
docker run --rm -d --add-host=host.docker.internal:host-gateway -p 4444:4444 -p 7900:7900 --shm-size="2g" selenium/standalone-chrome:latest
