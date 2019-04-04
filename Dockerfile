FROM ubuntu:16.04

# Install PHP, Apache, Git, Composer and all other necessary packages -> extension if necessary
RUN apt-get update \
    && apt-get install -y apt-utils\
    debconf-utils\
    openjdk-8-jdk\
    php\
    php-cli\
    php-common\
    php-curl\
    php-dev\
    php-gd\
    php-mcrypt\
    php-mysql\
    php-uuid\
    php-xsl\
    php-log\
    php-zip\
    libapache2-mod-php7.0\
    libxml2-utils\
    composer\
    git\
    wget\
    unzip\
    ant\
    curl\
    sudo\
    apache2

# Install MySQL
RUN echo "mysql-server-5.5 mysql-server/root_password password root" | debconf-set-selections \
    && echo "mysql-server-5.5 mysql-server/root_password_again password root" | debconf-set-selections \
    && apt-get -y install mysql-server \
    && service mysql start \
    && export MYSQL_PWD=root \
    && mysql --default-character-set=utf8 -h 'localhost' -P '3306' -u 'root' -v -e "CREATE DATABASE IF NOT EXISTS opusdb DEFAULT CHARACTER SET = UTF8 DEFAULT COLLATE = UTF8_GENERAL_CI; GRANT ALL PRIVILEGES ON opusdb.* TO 'opus4'@'localhost' IDENTIFIED BY 'root'; GRANT SELECT,INSERT,UPDATE,DELETE ON opusdb.* TO 'opus4admin'@'localhost' IDENTIFIED BY 'root'; FLUSH PRIVILEGES;"

# Download and unzip
Run cd \
    && wget https://www.apache.org/dist/lucene/solr/7.7.1/solr-7.7.1.zip \
    && unzip solr-7.7.1.zip -d . \
    && cp -a solr-7.7.1/. /opt/solr

# Download OPUS4 and install dependencies
Run cd \
    && git clone https://github.com/OPUS4/application.git \
    && cd application \
    && composer install \
    && useradd opus4

# Set Solr-Core
Run cd \
    && useradd -d /opt/solr solr \
    && cp /opt/solr/bin/init.d/solr /etc/init.d/ && mv /opt/solr/bin/solr.in.sh /etc/default/ \
    && chown solr:solr -R /opt/solr \
    && chmod a+x /etc/init.d/solr \
    && update-rc.d solr defaults \
    && service solr start \
    && mkdir /var/solr && mkdir /var/solr/data && mkdir /var/solr/data/opus4 \
    && cp /opt/solr/server/solr/solr.xml /var/solr/data \
    && cp ~/application/vendor/opus4-repo/search/core.properties /var/solr/data/opus4 \
    && cp ~/application/vendor/opus4-repo/search/schema.xml /var/solr/data/opus4 && mv /var/solr/data/opus4/schema.xml /var/solr/data/opus4/schema.xml \
    && cp ~/application/vendor/opus4-repo/search/solrconfig.xml /var/solr/data/opus4 && mv /var/solr/data/opus4/solrconfig.xml /var/solr/data/opus4/solrconfig.xml \
    && chown solr:solr -R /var/solr

Run echo "SOLR_PID_DIR="/var/solr"" >> /etc/default/solr.in.sh \
    && echo "SOLR_HOME="/var/solr/data"" >> /etc/default/solr.in.sh \
    && echo "LOG4J_PROPS="/var/solr/log4j.properties"" >> /etc/default/solr.in.sh \
    && echo "SOLR_LOGS_DIR="/var/solr/logs"" >> /etc/default/solr.in.sh \
    && echo "SOLR_PORT="8983"" >> /etc/default/solr.in.sh
