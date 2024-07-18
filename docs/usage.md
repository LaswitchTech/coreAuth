# Usage
## Initiate Auth
To use `Auth`, simply include the Auth.php file and create a new instance of the `Auth` class.

```php
<?php

// Import additionnal class into the global namespace
use LaswitchTech\coreAuth\Auth;

// Load Composer's autoloader
require 'vendor/autoload.php';

// Initiate Auth
$Auth = new Auth();
```

### Properties
`Auth` provides the following properties:

#### core Modules
- [Configurator](https://github.com/LaswitchTech/coreConfigurator)
- [Database](https://github.com/LaswitchTech/coreDatabase)
- [Logger](https://github.com/LaswitchTech/coreLogger)
- [CSRF](https://github.com/LaswitchTech/coreCSRF)

#### Auth Modules
- [Authentication](modules/Authentication/usage.md)
    - [is2FAReady()](modules/Authentication/methods/is2FAReady.md)
    - [isVerified()](modules/Authentication/methods/isVerified.md)
    - [error()](modules/Authentication/methods/error.md)
    - [status()](modules/Authentication/methods/status.md)
    - [isConnected()](modules/Authentication/methods/isConnected.md)
    - [isAuthenticated()](modules/Authentication/methods/isAuthenticated.md)
    - [logout()](modules/Authentication/methods/logout.md)
- [Authorization](modules/Authorization/usage.md)
    - [isAuthorized()](modules/Authorization/methods/isAuthorized.md)
    - [hasPermission()](modules/Authorization/methods/hasPermission.md)
- [Compliance](modules/Compliance/usage.md)
    - [form()](modules/Compliance/methods/form.md)
- [Installer](modules/Installer/usage.md)
    - [create()](modules/Compliance/methods/create.md)
- [Management](modules/Management/usage.md)
    - [create()](modules/Management/methods/create.md)
    - [read()](modules/Management/methods/read.md)
    - [update()](modules/Management/methods/update.md)
    - [delete()](modules/Management/methods/delete.md)

#### Auth Types
- [Basic](types/Basic/usage.md)
    - [getAuthentication()](types/Basic/methods/getAuthentication.md)
- [Bearer](types/Bearer/usage.md)
    - [getAuthentication()](types/Bearer/methods/getAuthentication.md)
- [Cookie](types/Cookie/usage.md)
    - [getAuthentication()](types/Cookie/methods/getAuthentication.md)
    - [set()](types/Cookie/methods/set.md)
    - [save()](types/Cookie/methods/save.md)
    - [clear()](types/Cookie/methods/clear.md)
- [Request](types/Request/usage.md)
    - [getAuthentication()](types/Request/methods/getAuthentication.md)
    - [is2FAReady()](types/Request/methods/is2FAReady.md)
- [Session](types/Session/usage.md)
    - [getAuthentication()](types/Session/methods/getAuthentication.md)
    - [save()](types/Session/methods/save.md)
    - [clear()](types/Session/methods/clear.md)

#### Auth Objects
- [User](objects/User/usage.md)
- [Group](objects/Group/usage.md)
- [Role](objects/Role/usage.md)
- [Organization](objects/Organization/usage.md)
- [Relationship](objects/Relationship/usage.md)
- [Permission](objects/Permission/usage.md)

### Methods
`Auth` provides the following methods:

#### General
- [config()](Auth/config.md)
- [install()](Auth/install.md)
- [manage()](Auth/manage.md)
