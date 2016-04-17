#!/usr/bin/env bash

# Install gcloud
if [ ! -d ${HOME}/gcloud/google-cloud-sdk ]; then
    mkdir -p ${HOME}/gcloud &&
    wget https://dl.google.com/dl/cloudsdk/release/google-cloud-sdk.tar.gz --directory-prefix=${HOME}/gcloud &&
    cd "${HOME}/gcloud" &&
    tar xzf google-cloud-sdk.tar.gz &&
    ./google-cloud-sdk/install.sh --usage-reporting false --path-update false --command-completion false &&
    cd "${TRAVIS_BUILD_DIR}";
fi

gcloud config set project ${GOOGLE_PROJECT_ID}
gcloud config set app/promote_by_default true
gcloud config set app/use_cloud_build true
gcloud auth activate-service-account --key-file \
    "${TRAVIS_BUILD_DIR}/client-secret.json"
gcloud -q components install app-engine-python
gcloud -q components install app-engine-php
# pinning to 104.0.0 because 105.0.0 is broken for php app
gcloud -q components update --version 104.0.0

gcloud preview app deploy app.yaml --stop-previous-version --force
