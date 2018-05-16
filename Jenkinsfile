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
    stage "checkout"
    checkout scm

    stage "prepare"
    docker.image('mysql:5').withRun('-e "MYSQL_ROOT_PASSWORD=root" -p 3309:3306') { c ->
        docker.image('mysql:5').inside("--link ${c.id}:db") {
            sh 'while ! mysqladmin ping -h0.0.0.0 --silent; do sleep 1; done'
        }
                docker.image('solr').inside("--link ${c.id}:db") {

        stage "build"
        sh 'ant setup prepare lint'

    }
}