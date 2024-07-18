# config(string $option, string $value)
This method is used to set the configuration options for the module.

```php
$Auth->config('option', 'value');
```

## Available Options
- maxAttempts: This option sets the maximum number of login attempts allowed.
- maxRequests: This option sets the maximum number of requests allowed.
- lockoutDuration: This option sets the duration of the lockout.
- windowAttempts: This option sets the window of login attempts.
- windowRequests: This option sets the window of requests.
- window2FA: This option sets the window of 2FA.
- windowVerification: This option sets the window of verification.
- basic: This option enables basic authentication.
- bearer: This option enables bearer authentication.
- request: This option enables request authentication.
- cookie: This option enables cookie authentication.
- session: This option enables session authentication.
- 2fa: This option enables 2FA.
- hostnames: This option sets the hostnames allowed.
