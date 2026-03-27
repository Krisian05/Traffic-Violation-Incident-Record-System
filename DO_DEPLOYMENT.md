# DigitalOcean Deployment Plan

Recommended DigitalOcean stack for this project:

- App Platform for hosting
- Managed MySQL for database
- Spaces for uploaded files

## Why This Is The Best DO Setup

- The project already uses MySQL, so Managed MySQL fits directly.
- App Platform is easier than managing a raw Droplet.
- App Platform local filesystem is not persistent, so uploads should go to Spaces.
- Spaces is S3-compatible, which matches the app's current configurable uploads disk.

References:

- App Platform details: https://docs.digitalocean.com/products/app-platform/details/
- App Platform limits: https://docs.digitalocean.com/products/app-platform/details/limits/
- App Platform pricing: https://docs.digitalocean.com/products/app-platform/details/pricing/
- Managed Databases pricing: https://www.digitalocean.com/pricing/managed-databases
- Spaces pricing: https://docs.digitalocean.com/products/spaces/details/pricing/
- Spaces S3 compatibility: https://docs.digitalocean.com/products/spaces/how-to/use-aws-sdks/

## Practical Cost Estimate

Typical starting monthly cost:

- App Platform web service: about `$12/month` for `professional-xs`, `$25/month` for `professional-s`
- Managed MySQL: typically starts around `$15+/month`
- Spaces: `$5/month` base, including `250 GiB` storage

Practical starting total:

- about `$32 to $45+/month`

## Recommended Setup

### 1. App Platform

- Connect your GitHub repo.
- Deploy the app as a web service.
- Start with a paid professional plan, not the static free tier.

### 2. Managed MySQL

- Create a Managed MySQL cluster.
- Create the database and DB user for the app.
- Copy host, port, database, username, and password.

### 3. Spaces

- Create one Space for uploads.
- Generate access key and secret key.
- Note the region and endpoint.

## Required Environment Variables

Use [`.env.do.example`](/c:/xampp/htdocs/TVRS%20V2/.env.do.example) as the starting point.

Important values:

- `DB_CONNECTION=mysql`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `SESSION_DRIVER=database`
- `UPLOADS_DISK=s3`
- `CACHE_STORE=file`
- `QUEUE_CONNECTION=sync`
- `LOG_CHANNEL=stderr`
- `AWS_ACCESS_KEY_ID`
- `AWS_SECRET_ACCESS_KEY`
- `AWS_BUCKET`
- `AWS_ENDPOINT`

Why the session setting matters:

- App Platform can run more than one web instance.
- `SESSION_DRIVER=file` stores sessions separately on each instance.
- That causes random or repeated `419 Page Expired` errors when requests land on different instances.
- `SESSION_DRIVER=database` avoids that by sharing sessions through MySQL.

## First Deployment Commands

Make sure the deployment process runs:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Rehearsal Test

Before calling it ready:

- log in as admin
- create a motorist with photo
- create a violation with uploaded images
- create an incident with media
- verify all uploaded images load
- test edits and deletes

## Best Domain

- Use a real custom domain, not a platform subdomain
- If this becomes an official police deployment, move to the official agency domain later
