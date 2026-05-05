#!/bin/bash

# QuizNote Quick Deploy - One-line installation script
# Run on your CentOS/RHEL server as root:
# curl -fsSL https://raw.githubusercontent.com/bangmaix/QuizNote/main/quick-deploy.sh | bash

cd /tmp
git clone https://github.com/bangmaix/QuizNote.git quiznote-deploy
cd quiznote-deploy
bash deploy-production.sh
