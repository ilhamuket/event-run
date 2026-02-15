pipeline {
  agent any

  options {
    timestamps()
    disableConcurrentBuilds()
  }

  environment {
    TZ = 'Asia/Jakarta'
  }

  stages {

    stage('Set Build Info') {
      steps {
        script {
          def timeWIB = sh(
            script: "date '+%Y-%m-%d %H:%M:%S'",
            returnStdout: true
          ).trim()

          currentBuild.displayName = "#${BUILD_NUMBER} - ${timeWIB} WIB"
          currentBuild.description = "Build pada ${timeWIB} WIB"
        }
      }
    }

    stage('Prepare Env') {
      steps {
        script {
          // SINGLE SOURCE OF TRUTH
          def branchConfig = [
            main: [
              sshUser : 'devops',
              sshCred : 'ssh-host-main',
              script  : '/home/devops/cicd/main/deploy.event-run.main.sh'
            ],            
            production: [
              sshUser : 'devops',
              sshCred : 'ssh-host-production',
              script  : '/home/devops/cicd/main/deploy.event-run.production.sh'
            ],
            staging: [
              sshUser : 'devops',
              sshCred : 'ssh-host-staging',
              script  : '/home/devops/cicd/main/deploy.event-run.staging.sh'
            ],
            dev: [
              sshUser : 'devops',
              sshCred : 'ssh-host-dev',
              script  : '/home/devops/cicd/main/deploy.event-run.dev.sh'
            ]
          ]

          def branch = env.BRANCH_NAME ?: 'dev'

          if (!branchConfig.containsKey(branch)) {
            error "❌ Branch '${branch}' tidak diizinkan untuk deploy"
          }

          env.DEPLOY_BRANCH   = branch
          env.SSH_USER        = branchConfig[branch].sshUser
          env.DEPLOY_SSH_CRED = branchConfig[branch].sshCred
          env.DEPLOY_SCRIPT  = branchConfig[branch].script

          echo """
          Deploy Configuration:
          - Branch : ${env.DEPLOY_BRANCH}
          - User   : ${env.SSH_USER}
          - Script : ${env.DEPLOY_SCRIPT}
          """
        }
      }
    }

    stage('Deploy') {
      steps {
        withCredentials([
          string(credentialsId: env.DEPLOY_SSH_CRED, variable: 'SSH_HOST')
        ]) {
          sshagent(['github-ssh']) {
            sh """
              set -e
              echo "🚀 Deploying to ${SSH_USER}@${SSH_HOST}"
              ssh -o StrictHostKeyChecking=no \
                  -o ServerAliveInterval=30 \
                  -p 22 ${SSH_USER}@${SSH_HOST} \
                  'bash ${DEPLOY_SCRIPT}'
            """
          }
        }
      }
    }
  }

  post {
    always {
      cleanWs()
    }

    success {
      echo "✅ Deploy ${env.DEPLOY_BRANCH} SUCCESS"
    }

    failure {
      echo "❌ Deploy ${env.DEPLOY_BRANCH} FAILED"
    }
  }
}
