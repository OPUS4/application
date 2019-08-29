FROM ubuntu:16.04

# Update Ubuntu
RUN apt-get update

# Install system-packages
RUN apt-get update && apt-get install -y debconf-utils\
    composer\
    openjdk-8-jdk\
    wget\
    unzip\
    ant\
    apache2\
    libxml2-utils\
    libapache2-mod-php7.0\
    sudo

# Install PHP
RUN apt-get install -y php\
    php-cli\
    php-dev\
    php-mbstring\
    php-mysql\
    php-curl\
    php-gd\
    php-common\
    php-intl\
    php-zip\
    php-uuid\
    php-xsl\
    php-log\
    php-mcrypt
