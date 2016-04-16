# Le Schett Bott

## Running it locally

Requires google cloud SDK.

```
 # authenticates the local sdk
gcloud init
# projectId is required for the local datastore.
# can be an invalid string
python2 ~/google-cloud-sdk/bin/dev_appserver.py --project $projectId app.yaml
```

## Deployment time

```
gcloud preview app deploy --project $projectId
```
