# syntax=docker/dockerfile:1

FROM ghcr.io/linuxserver/baseimage-alpine-nginx:3.22

# set version label
ARG BUILD_DATE
ARG VERSION
ARG COPS_RELEASE
LABEL build_version="Linuxserver.io version:- ${VERSION} Build-date:- ${BUILD_DATE}"
LABEL maintainer="chbmb"

RUN \
  echo "**** install runtime packages ****" && \
  apk add --no-cache --upgrade \
    # libxml2 \
    icu-data-full \
    php84-dom \
    php84-gd \
    php84-intl \
    php84-pdo_sqlite \
    php84-sqlite3 && \
  echo "**** configure php-fpm to pass env vars ****" && \
  sed -E -i 's/^;?clear_env ?=.*$/clear_env = no/g' /etc/php84/php-fpm.d/www.conf && \
  if ! grep -qxF 'clear_env = no' /etc/php84/php-fpm.d/www.conf; then echo 'clear_env = no' >> /etc/php84/php-fpm.d/www.conf; fi && \
  echo "env[PATH] = /usr/local/bin:/usr/bin:/bin" >> /etc/php84/php-fpm.conf && \
  echo "**** install cops ****" && \
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
  composer \
    install --no-dev --optimize-autoloader && \
  printf "Linuxserver.io version: ${VERSION}\nBuild-date: ${BUILD_DATE}" > /build_version && \
  echo "***install kepubify" && \
  if [ -z ${KEPUBIFY_RELEASE+x} ]; then \
    KEPUBIFY_RELEASE=$(curl -sX GET "https://api.github.com/repos/pgaskin/kepubify/releases/latest" \
      | awk '/tag_name/{print $4;exit}' FS='[""]'); \
  fi && \
  curl -o \
    /usr/bin/kepubify -L \
    "https://github.com/pgaskin/kepubify/releases/download/${KEPUBIFY_RELEASE}/kepubify-linux-64bit" && \
  chmod 755 /usr/bin/kepubify && \
  echo "**** cleanup ****" && \
  rm -rf \
    /root/.composer \
    /tmp/*

# add local files
COPY root/ /

# ports and volumes
EXPOSE 80 443
VOLUME /config
