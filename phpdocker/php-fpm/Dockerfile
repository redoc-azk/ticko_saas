FROM phpdockerio/php:8.2-fpm
WORKDIR "/application"

RUN apt-get update; \
    apt-get -y --no-install-recommends install \
        git \ 
        php8.2-bz2 \ 
        php8.2-decimal \ 
        php8.2-enchant \ 
        php8.2-gd \ 
        php8.2-http \ 
        php8.2-imap \ 
        php8.2-intl \ 
        php8.2-mysql \ 
        php8.2-redis \ 
        php8.2-uuid \ 
        php8.2-xdebug \ 
        php8.2-yaml; \
    curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | sudo -E bash; \
    apt -y install symfony-cli; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*
