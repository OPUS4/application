#!/usr/bin/env bash

# Download and unpack Solr
sudo wget https://archive.apache.org/dist/lucene/solr/7.7.1/solr-7.7.1.zip \
    && unzip -o solr-7.7.1.zip -d .

# Add User
sudo useradd -d /opt/solr solr && useradd opus4

# Install Solr and set permissions
sudo cp -rf solr-7.7.1/. /opt/solr \
    && cp /opt/solr/bin/init.d/solr /etc/init.d/ \
    && mv /opt/solr/bin/solr.in.sh /etc/default/ \
    && chown solr:solr -R /opt/solr \
    && chmod a+x /etc/init.d/solr \
    && update-rc.d solr defaults

# Create folders for core-installation
sudo mkdir /var/solr && mkdir /var/solr/data && mkdir /var/solr/data/opus4

# Install opus4-core
sudo cp /opt/solr/server/solr/solr.xml /var/solr/data \
    && cp vendor/opus4-repo/search/core.properties /var/solr/data/opus4/core.properties \
    && cp vendor/opus4-repo/search/config/schema.xml /var/solr/data/opus4/schema.xml \
    && cp vendor/opus4-repo/search/config/solrconfig.xml /var/solr/data/opus4/solrconfig.xml \
    && chown solr:solr -R /var/solr

# Configure Solr
sudo echo "SOLR_PID_DIR="/var/solr"" >> /etc/default/solr.in.sh \
    && echo "SOLR_HOME="/var/solr/data"" >> /etc/default/solr.in.sh \
    && echo "LOG4J_PROPS="/var/solr/log4j.properties"" >> /etc/default/solr.in.sh \
    && echo "SOLR_LOGS_DIR="/var/solr/logs"" >> /etc/default/solr.in.sh \
    && echo "SOLR_PORT="8983"" >> /etc/default/solr.in.sh