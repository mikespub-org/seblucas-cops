# syntax=docker/dockerfile:1

# update to alpine 3.18 from archived linuxserver/docker-cops version
FROM ghcr.io/linuxserver/baseimage-alpine-nginx:3.18

# set version label
ARG BUILD_DATE
ARG VERSION
ARG COPS_RELEASE
LABEL build_version="Linuxserver.io version:- ${VERSION} Build-date:- ${BUILD_DATE}"
LABEL maintainer="chbmb"

RUN \
  echo "**** install runtime packages ****" && \
  # update to PHP 8.2 from archived linuxserver/docker-cops version
  apk add --no-cache --upgrade \
    # libxml2 \
    php82-dom \
    php82-gd \
    php82-intl \
    # php82-json \
    php82-mbstring \
    php82-pdo_sqlite \
    php82-sqlite3 \
    php82-xml \
    php82-xmlwriter \
    php82-zip && \
  echo "**** configure php-fpm to pass env vars ****" && \
  sed -E -i 's/^;?clear_env ?=.*$/clear_env = no/g' /etc/php82/php-fpm.d/www.conf && \
  grep -qxF 'clear_env = no' /etc/php82/php-fpm.d/www.conf || echo 'clear_env = no' >> /etc/php82/php-fpm.d/www.conf && \
  echo "env[PATH] = /usr/local/bin:/usr/bin:/bin" >> /etc/php82/php-fpm.conf && \
  echo "**** install cops ****" && \
  # use fork mikespub-org/seblucas-cops for PHP 8.x
  if [ -z ${COPS_RELEASE+x} ]; then \
    COPS_RELEASE=$(curl -sX GET "https://api.github.com/repos/mikespub-org/seblucas-cops/releases/latest" \
    | awk '/tag_name/{print $4;exit}' FS='[""]'); \
  fi && \
  curl -o \
    /tmp/cops.tar.gz -L \
    "https://github.com/mikespub-org/seblucas-cops/archive/${COPS_RELEASE}.tar.gz" && \
  mkdir -p \
    /app/www/public && \
  tar xf /tmp/cops.tar.gz -C \
    /app/www/public --strip-components=1 && \
  cd /app/www/public && \
  # use standard composer 2.x now, no need to install older 1.x version
  composer \
    install --no-dev --optimize-autoloader && \
  echo "**** cleanup ****" && \
  rm -rf \
    /root/.composer \
    /tmp/*

# add local files
COPY root/ /

# ports and volumes
EXPOSE 80 443
VOLUME /config
