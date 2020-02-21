def jobNameParts = JOB_NAME.tokenize('/') as String[]
def projectName = jobNameParts[0]
def buildType = "short"

if (projectName.contains('night') && (env.BRANCH_NAME == '4.7' || env.BRANCH_NAME == 'master')) {
    buildType = "long"
}
pipeline {
    agent { dockerfile {args "-u root -v /var/run/docker.sock:/var/run/docker.sock"}}
    environment {XML_CATALOG_FILES = "${WORKSPACE}/tests/resources/opus4-catalog.xml"}

    triggers {
        cron( buildType.equals('long') ? 'H 21 * * 5' : '')
    }

    stages {
        stage('Composer') {
            steps {
                sh 'sudo apt-get update'
                sh 'curl -s http://getcomposer.org/installer | php && php composer.phar self-update && php composer.phar install'
            }
        }

        stage('Solr') {
            steps {
                sh 'sudo bash bin/install_solr_docker.sh'
            }
        }

        stage('MySQL') {
            steps {
                sh 'sudo bash bin/install_mysql_docker.sh'
            }
        }

        stage('Prepare Opus4') {
            steps {
                sh 'ant prepare-workspace prepare-test-workspace prepare-javascript prepare-config lint -DdbUserPassword=root -DdbAdminPassword=root'
                sh 'pecl install xdebug-2.8.0 && echo "zend_extension=/usr/lib/php/20151012/xdebug.so" >> /etc/php/7.0/cli/php.ini'
                sh 'php ${WORKSPACE}/scripts/opus-smtp-dumpserver.php 2>&1 >> ${WORKSPACE}/tests/workspace/log/opus-smtp-dumpserver.log &'
                sh 'chown -R opus4:opus4 .'
            }
        }

        stage('Analyse') {
            steps {
                script{
                   sh 'php composer.phar analysis'
                }
            }
        }

        stage('Test') {
            steps {
                script{
                    sh 'ant reset-testdata'
                    if (buildType == 'long'){
                        sh 'php composer.phar test-coverage'
                    } else {
                        sh 'php composer.phar test'
                    }
                }
            }
        }
    }

    post {
        always {
            sh "chmod -R 777 ."
            step([
                $class: 'JUnitResultArchiver',
                testResults: 'build/phpunit.xml'
            ])
            step([
                $class: 'hudson.plugins.checkstyle.CheckStylePublisher',
                pattern: 'build/checkstyle.xml'
            ])
            step([
                $class: 'hudson.plugins.dry.DryPublisher',
                pattern: 'build/pmd-cpd.xml'
            ])
            step([
                $class: 'hudson.plugins.pmd.PmdPublisher',
                pattern: 'build/pmd.xml'
            ])
            step([
                $class: 'CloverPublisher',
                cloverReportDir: 'build',
                cloverReportFileName: 'clover.xml"'
            ])
            step([$class: 'WsCleanup', externalDelete: 'rm -rf *'])
        }
    }
}
