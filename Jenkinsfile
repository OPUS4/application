def jobNameParts = JOB_NAME.tokenize('/') as String[]
def projectName = jobNameParts[0]
def buildType = "short"

if (projectName.contains('night')) {
    buildType = "long"
}
pipeline {
    agent { dockerfile {args "-u root -v /var/run/docker.sock:/var/run/docker.sock"}}
    environment {XML_CATALOG_FILES = "${WORKSPACE}/tests/resources/opus4-catalog.xml"}

    triggers {
        cron( buildType.equals('long') ? 'H 3 * * *' : '')
    }

    stages {
        stage('Composer') {
            steps {
                sh 'composer install'
                sh 'sudo apt-get update'
            }
        }

        stage('Solr') {
            steps {
                sh 'sudo bash bin/install_solr_docker.sh'
                sh 'sudo service solr start'
            }
        }

        stage('MySQL') {
            steps {
                sh 'sudo bash bin/install_mysql_docker.sh'
            }
        }

        stage('Prepare Opus4') {
            steps {
                sh 'ant setup lint -DdbUserPassword=root -DdbAdminPassword=root'
                sh 'php ${WORKSPACE}/scripts/opus-smtp-dumpserver.php 2>&1 >> ${WORKSPACE}/tests/workspace/log/opus-smtp-dumpserver.log &'
                sh 'chown -R opus4:opus4 .'
            }
        }

        stage('Test') {
            steps {
                script{
                    switch (buildType) {
                        case "long":
                            sh 'sudo -E -u opus4 ant phpunit'
                            break
                        default:
                            sh 'sudo -E -u opus4 ant phpunit-fast'
                            break
                  }
                }
            }
        }

        stage('Analyse') {
            steps {
                script{
                   switch (buildType) {
                       case "long":
                           sh 'ant analyse-code'
                           breaek
                       default:
                            break
                   }
                }
            }
        }
    }

    post {
        always {
            step([
                $class: 'JUnitResultArchiver',
                testResults: 'build/logs/phpunit.xml'
            ])
            step([
                $class: 'hudson.plugins.checkstyle.CheckStylePublisher',
                pattern: 'build/logs/checkstyle.xml'
            ])
            step([
                $class: 'hudson.plugins.dry.DryPublisher',
                pattern: 'build/logs/pmd-cpd.xml'
            ])
            step([
                $class: 'hudson.plugins.pmd.PmdPublisher',
                pattern: 'build/logs/pmd.xml'
            ])
            step([$class: 'WsCleanup'])
        }
    }
}