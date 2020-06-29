FROM ubuntu:16.04

# Add opus4 user, because in the Jenkinsfile we are root. Some tests dont work with root, so we need to start them with opus4-user
RUN useradd opus4

# The parts of the script are combined by &&. If something changes, always the update of the system is done, to find new packages or versions.
# Update Ubuntu
RUN apt-get update\

# Install system-packages
&& apt-get install -y debconf-utils\
    composer\
    curl\
    openjdk-8-jdk\
    wget\
    unzip\
    ant\
    apache2\
    libxml2-utils\
    libapache2-mod-php7.0\
    sudo \

# Install PHP
&& apt-get install -y php\
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
