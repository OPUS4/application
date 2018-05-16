#!groovy
node {
    checkout scm
    docker.image('mysql:5').withRun('-e "MYSQL_ROOT_PASSWORD=root" -p 3309:3306') { c ->
         
            /* Wait until mysql service is up */
            sh 'while ! mysqladmin ping -hdb --silent; do sleep 1; done'

             stage "build"
             sh 'ant setup prepare lint'
        }
    }
}