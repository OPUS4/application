// Jenkinsfile for the application

// Define the project name and the build type. A short build dispenses with coverage, since coverage is very time-consuming
def jobNameParts = JOB_NAME.tokenize('/') as String[]
def projectName = jobNameParts[0]
def buildType = "short"

// If the project has "night" in its name and the master branch or the development branch is present, the build is defined as a long build
if (projectName.contains('night') && (env.BRANCH_NAME == '4.7' || env.BRANCH_NAME == 'master')) {
    buildType = "long"
}
pipeline {
    /*
    Agent (location where the pipeline is executed) is the docker file.
    This must have root privileges, because MySQL and Solr must be installed.
    Also, creating a docker on the server requires root privileges.
    Furthermore, the docker socket of the server must be connected to the docker socket of the docker.
    */
    agent { dockerfile {args "-u root -v /var/run/docker.sock:/var/run/docker.sock"}}
    environment {XML_CATALOG_FILES = "${WORKSPACE}/tests/resources/opus4-catalog.xml"}

    /*
    A long build will not be built on every commit because of the long duration, but will be time-triggered.
    Coverage takes so long that it is only built once a week for the development branch and the master branch
    */
    triggers {
        cron( buildType.equals('long') ? 'H 21 * * 5' : '')
    }

    stages {
        stage('Composer') {
            steps {
                // Update Operating system
                sh 'sudo apt-get update'

                // Install and update Composer. Additionally install dependencies of OPUS4-Application
                sh 'curl -s http://getcomposer.org/installer | php && php composer.phar self-update && php composer.phar install'
            }
        }

        stage('Install Solr') {
            steps {
                sh 'sudo bash bin/install_solr_docker.sh'
            }
        }

        stage('Install MySQL') {
            steps {
                sh 'sudo bash bin/install_mysql_docker.sh'
            }
        }

        stage('Prepare Opus4') {
            steps {
                // Prepare OPUS4 with ant using standard passwords (only test-system)
                sh 'ant prepare-workspace prepare-test-workspace prepare-javascript prepare-config lint -DdbUserPassword=root -DdbAdminPassword=root'

                // Install XDebug with Pecl -> Using apt-get would install a old version
                sh 'pecl install xdebug-2.8.0 && echo "zend_extension=/usr/lib/php/20151012/xdebug.so" >> /etc/php/7.0/cli/php.ini'

                // Install Mail-Server for mailing-tests
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
            /*
            For the cleanup the entire workspace is deleted.
            This reduces the server load, since Jenkins does not track the workspaces unnecessarily.
            It may be possible to turn off tracking, but I couldn't find an option.
            Jenkins must have permissions to delete the workspaces.
            */
            sh "chmod -R 777 ."

            // Publishing test-results (unit-tests)
            step([
                $class: 'JUnitResultArchiver',
                testResults: 'build/phpunit.xml'
            ])

            // Publishing checkstyle-results (coding-style)
            step([
                $class: 'hudson.plugins.checkstyle.CheckStylePublisher',
                pattern: 'build/checkstyle.xml'
            ])

            // Publishing CPD-results (find duplicated code-fragments)
            step([
                $class: 'hudson.plugins.dry.DryPublisher',
                pattern: 'build/pmd-cpd.xml'
            ])

            // Publishing PMD-results (static codeanalysis)
            step([
                $class: 'hudson.plugins.pmd.PmdPublisher',
                pattern: 'build/pmd.xml'
            ])

            // Publishing coverage-report if exists
            step([
                $class: 'CloverPublisher',
                cloverReportDir: 'build',
                cloverReportFileName: 'clover.xml"'
            ])

            // Cleanup
            step([$class: 'WsCleanup', externalDelete: 'rm -rf *'])
        }
    }
}
