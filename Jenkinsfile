pipeline {
    agent { dockerfile {args "-u root -v /var/run/docker.sock:/var/run/docker.sock"}}
    environment {XML_CATALOG_FILES = "${WORKSPACE}/tests/resources/opus4-catalog.xml"}

    stages {
        stage('Build') {
            steps {
                sh 'sudo service solr start'
                sh 'sudo chown -R mysql:mysql /var/lib/mysql /var/run/mysqld'
                sh 'sudo service mysql start'
                sh 'composer install'
                sh 'ant setup prepare lint prepare-config reset-testdata'
                sh 'php ${WORKSPACE}/scripts/opus-smtp-dumpserver.php 2>&1 >> ${WORKSPACE}/tests/workspace/log/opus-smtp-dumpserver.log &'
                sh 'chown -R opus4:opus4 .'
            }
        }

        stage('Test') {
            steps {
            sh 'sudo -E -u opus4 ant phpunit'
            }
        }
    }
}
