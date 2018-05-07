#!groovy

/**
  * We have use an hardcoded job_name, because we actually don't have a better solution.
  * So actually we made an different set_up for night-containing Jobs and for every other.
  * Additionaly we have a 3 am trigger for night-builds and an 0 am trigger for every other.
  * For MySQL we use a Docker.
  * Pay Attention on the Port. In Line 43, the first port have to be set on the port in the config.ini.
  */

def jobNameParts = JOB_NAME.tokenize('/') as String[]
def projectName = jobNameParts[0]

node {

    if(projectName.contains('night')){
        properties([
            parameters([
                string(name: 'DEPLOY_ENV', defaultValue: 'TRUE')
            ]),
            pipelineTriggers([
                cron('0 3 * * *')
            ]),
            disableConcurrentBuilds()
        ])
    }
    else{
        properties([
            parameters([
                string(name: 'DEPLOY_ENV', defaultValue: 'FALSE')
            ]),
            pipelineTriggers([
                cron('0 0 * * *')
            ]),
            disableConcurrentBuilds()
        ])
    }

    stage "checkout"
    checkout scm

    stage "prepare"
    docker.image('mysql:5').withRun('-e "MYSQL_ROOT_PASSWORD=root" -p 3306:3306') { c ->
        docker.image('mysql:5').inside("--link ${c.id}:db"){
            sh 'while ! mysqladmin ping -h0.0.0.0 --silent; do sleep 1; done'
        }
        docker.image('solr:7').withRun('-p 8983:8983'){

            stage "build"
            sh 'ant setup prepare lint'

            if ('${params.DEPLOY_ENV}' == 'TRUE')
            {
                stage "Test"
                sh 'ant phpunit'
            }
            else
            {
                stage "Test"
                sh 'ant phpunit-fast'
            }

            stage "analyse"
            sh 'ant analyse-code'

            stage('Post-Script') {
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

                step([
                    $class: 'hudson.plugins.sloccount.SloccountPublisher',
                    pattern: 'build/logs/phploc.csv'
                ])

                step([
                    $class: 'CloverPublisher',
                    cloverReportDir: 'build',
                    cloverReportFileName: 'build/logs/phpunit.coverage.xml'
                ])
            }
        }
    }