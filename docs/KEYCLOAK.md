# Keycloak Setup

This guide explains how to run Keycloak locally and configure it as the IdP for the Laravel API.

## 1. Start Keycloak

Start the Keycloak services from Docker Compose:

```bash
docker compose up -d keycloak keycloak-postgres
```

Keycloak UI:

```text
http://localhost:8080
```

Default local admin credentials from `docker-compose.yml`:

```text
Username: admin
Password: admin
```

## 2. Create The Realm

In the Keycloak admin console:

1. Open the realm selector.
2. Create a new realm named `yinyang`.
3. Switch into the `yinyang` realm before creating clients or users.

## 3. Create The Laravel Client

Create a client in the `yinyang` realm:

```text
Client ID: yinyang-api
Client type: OpenID Connect
Client authentication: On
Authorization: Off
Standard flow: On
Direct access grants: Off
```

Set the redirect URI to match Laravel:

```text
http://host.docker.internal:8000/api/v1/auth/keycloak/callback
```

For local browser-only testing, also allow:

```text
http://localhost:8000/api/v1/auth/keycloak/callback
```

Set web origins:

```text
http://host.docker.internal:8000
http://localhost:8000
```

Enable backchannel logout for the client:

```text
Backchannel logout: On
Backchannel logout URL: http://host.docker.internal:8000/api/v1/auth/keycloak/backchannel-logout
```

After saving the client, copy the client secret from the `Credentials` tab.

## 4. Configure Laravel Environment

Update `.env` with the client secret and local URLs:

```env
KEYCLOAK_BASE_URL=http://host.docker.internal:8080
KEYCLOAK_INTERNAL_BASE_URL=http://host.docker.internal:8080
KEYCLOAK_REALM=yinyang
KEYCLOAK_CLIENT_ID=yinyang-api
KEYCLOAK_CLIENT_SECRET=<copy-from-keycloak-client-credentials>
KEYCLOAK_REDIRECT_URI=http://host.docker.internal:8000/api/v1/auth/keycloak/callback
KEYCLOAK_BACKCHANNEL_LOGOUT_URI=http://host.docker.internal:8000/api/v1/auth/keycloak/backchannel-logout
```

Clear Laravel config and route cache after changing `.env`:

```bash
make command:laravel command="php artisan optimize:clear"
```

If `docker-compose.yml` was changed after the Laravel container was already running, recreate it so `host.docker.internal` is available inside the container:

```bash
docker compose up -d --force-recreate laravel
```

## 5. Create A Test User

In the `yinyang` realm:

1. Go to `Users`.
2. Create a user.
3. Set an email address.
4. Turn `Email verified` on if the Laravel user should be marked verified after login.
5. Open the `Credentials` tab and set a password.
6. Turn `Temporary` off for local testing.

## 6. Test The Login Flow

Request the redirect URL from Laravel:

```bash
curl http://localhost:8000/api/v1/auth/keycloak/redirect
```

Open the returned `redirect_url` in the browser, log in with the Keycloak user, and Keycloak will redirect back to:

```text
/api/v1/auth/keycloak/callback
```

Laravel will create or link the local user account and return a Sanctum bearer token. The token is linked to Keycloak's `sid` claim so Keycloak backchannel logout can revoke it later:

```json
{
  "access_token": "...",
  "token_type": "Bearer",
  "user": {
    "data": {
      "id": "...",
      "email": "..."
    }
  }
}
```

Use the token with protected API routes:

```bash
curl http://localhost:8000/api/v1/profile \
  -H "Authorization: Bearer <access_token>" \
  -H "Accept: application/json"
```

## Troubleshooting

### Invalid redirect URI

Check the client `Valid redirect URIs` in Keycloak. It must include the exact callback URL from `KEYCLOAK_REDIRECT_URI`.

### Invalid client credentials

Check `KEYCLOAK_CLIENT_SECRET` in `.env`, then run:

```bash
make command:laravel command="php artisan optimize:clear"
```

### Laravel cannot reach Keycloak

From the Laravel container, the configured base URL should resolve:

```bash
make command:laravel command='php -r "echo file_get_contents(\'http://host.docker.internal:8080/realms/yinyang/.well-known/openid-configuration\');"'
```

If this fails, recreate the Laravel container:

```bash
docker compose up -d --force-recreate laravel
```

### User is not email verified in Laravel

Set `Email verified` to on in the Keycloak user profile before logging in.
