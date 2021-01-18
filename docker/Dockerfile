FROM unocha/unified-builder:7.3-stable as builder
ARG  BRANCH_ENVIRONMENT
ENV  NODE_ENV=$BRANCH_ENVIRONMENT
COPY . /srv/www
WORKDIR /srv/www
# Clean up previous composer installation and run new one.
RUN rm -rf ./vendor && \
    composer install
# Clean up previous npm installation and run new one. Those commands correspond
# to running `composer gulp` but without hitting the PHP timeout.
RUN cd html/themes/custom/common_design_subtheme && \
    rm -rf ./node_modules && \
    npm install && \
    ./node_modules/.bin/gulp build && \
    rm -rf ./node_modules
# Copy settings to default site location.
RUN cp -a docker/settings.php docker/services.yml html/sites/default

FROM unocha/php7-k8s:${TAG:-7.3-NR-stable}

ARG VCS_REF
ARG VCS_URL
ARG BUILD_DATE
ARG GITHUB_ACTOR
ARG GITHUB_REPOSITORY
ARG GITHUB_SHA
ARG GITHUB_REF

ENV  NGINX_SERVERNAME=odsg.unocha.org
ENV  PHP_ENVIRONMENT=production
ENV  PHP_MEMORY_LIMIT=256M
ENV  PHP_MAX_CHILDREN=16
ENV  GIT_BLAME=$GITHUB_ACTOR
ENV  GIT_REPO=$GITHUB_REPOSITORY
ENV  GIT_SHA=$GITHUB_SHA
ENV  GIT_REF=$GITHUB_REF
LABEL info.humanitarianresponse.build.date=$BUILD_DATE \
      info.humanitarianresponse.build.vcs-url=$VCS_URL \
      info.humanitarianresponse.build.vcs-ref=$VCS_REF
# Add PHP Soap extension to request OCHA Contribution Tracking System API.
RUN apk -U add php7-soap

COPY --from=builder /srv/www/config /srv/www/config/
COPY --from=builder /srv/www/html /srv/www/html/
COPY --from=builder /srv/www/vendor /srv/www/vendor/
COPY --from=builder /srv/www/composer.json /srv/www/composer.json
COPY --from=builder /srv/www/composer.patches.json /srv/www/composer.patches.json
COPY --from=builder /srv/www/composer.lock /srv/www/composer.lock
COPY --from=builder /srv/www/docker/fastcgi_drupal.conf /etc/nginx/apps/drupal/fastcgi_drupal.conf
