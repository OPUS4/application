#!groovy

/**
  * This Jenkinsfile is using to configurate an jenkins-job for testing at an CI-System
  * For this a env-variable is used. This is for activate a short or an normal build.
  * A short build do not check the coverage
  * The short build will be activated, if the job_name has an 'night'-substring. This is actually hardcoded.
  * So actually we made an different setup for night-containing jobs and for every other.
  * Additionaly we have a 3 am trigger for night-builds(normal) and an 0 am trigger for every other.
  * For MySQL we use a Docker.
  * Pay Attention on the Port. In Line 49, the first port have to be set on the port in the config.ini
  *
  * TODO: Actually the normal build is also triggered with an push on github. We want, that short build are always
  * triggerd with an push on github, but one time a day for minimum. Normal builds only triggered at night an 3 am.
  */

def jobNameParts = JOB_NAME.tokenize('/') as String[]
def projectName = jobNameParts[0]


node {
environment{
    def XML_CATALOG_FILES="${WORKSPACE}/tests/resources/opus4-catalog.xml"
}
    if(projectName.contains('night')){
        properties([
            parameters([
                string(name: 'Short_Build', defaultValue: 'FALSE')
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
                string(name: 'Short_Build', defaultValue: 'TRUE')
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
    docker.image('mysql:5').withRun('-e "MYSQL_ROOT_PASSWORD=root" -p 3308:3306') { c ->
        sh 'while ! mysqladmin ping -h0.0.0.0 --silent; do sleep 1; done'

        stage "build"
        sh 'ant setup prepare lint prepare-config'
        sh 'echo ${XML_CATALOG_FILES}'

        if ('${params.Short_Build}' == 'TRUE')
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
