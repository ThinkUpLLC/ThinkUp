# ThinkUp Docker Setup
#
# Pre-requisite:
# 0. Adjust your target mailserver in the variable MAIL_RELAY below to your
#    liking. The default only works for Google-hosted domains.
#
# If you don't have an existing MySQL instance, use the included
# docker-compose template:
# 1. Run `docker-compose build` to build the image.
# 2. Run `docker-compose up` to run both the mysql and thinkup containers.
# 3. Use a browser for the initial configuration and database setup.
#    (Use `mysql` as the hostname, `root` as the database user, and `thinkup`
#    as the password for the database.)
# 4. Run the database migrations via
#    `docker exec thinkup_thinkup_1 php install/cli/upgrade.php --with-new-sql`
#
# Directions without Docker Compose:
# 1. Build the Docker image via `docker build -t thinkup .
# 2. Run the Docker container from the image via
#    `docker run -p 80:80 --name thinkup thinkup
# 3. Use a browser for the initial configuration and database setup
# 4. Run the database migrations via
#    `docker exec thinkup php install/cli/upgrade.php --with-new-sql`

FROM php:5.6-apache

ENV MAIL_RELAY=aspmx.l.google.com
ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
  && apt-get install -y zlib1g-dev libpng12-dev ssmtp \
  && docker-php-ext-install gd pdo pdo_mysql zip

ADD webapp /var/www/html
RUN echo "session.save_path = '/tmp'" > /usr/local/etc/php/conf.d/session_save_path.ini \
  && echo "sendmail_path = '/usr/lib/sendmail -t -i'" > /usr/local/etc/php/conf.d/sendmail_path.ini \
  && sed -i -e "s/^mailhub=.*/mailhub=$MAIL_RELAY/g" /etc/ssmtp/ssmtp.conf \
  && sed -i -e "s/^hostname=.*/hostname=thinkup.docker.local/g" /etc/ssmtp/ssmtp.conf \
  && chmod -R 777 /var/www/html/data/
