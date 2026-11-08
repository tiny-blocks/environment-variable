# Environment variable

[![License](https://img.shields.io/badge/license-MIT-green)](https://github.com/tiny-blocks/environment-variable/blob/main/LICENSE)

* [Overview](#overview)
* [Installation](#installation)
* [How to use](#how-to-use)
    + [Application environment](#application-environment)
    + [Reading a variable](#reading-a-variable)
    + [Conversions](#conversions)
    + [Checking for a value](#checking-for-a-value)
    + [Exceptions](#exceptions)
    + [Resolution order](#resolution-order)
* [FAQ](#faq)
* [License](#license)
* [Contributing](#contributing)

<div id='overview'></div>

## Overview

A type-safe environment variable reader for PHP. A variable converts to string, integer, float, or boolean, raising a
dedicated exception instead of coercing a value it cannot represent, and tells which environment the application runs
in through predicates such as `isLocal()` and `isProduction()`, so code never compares raw strings.

Names in the `HTTP_` namespace are read only from sources an HTTP request cannot reach, so a request header is never
mistaken for configuration. See [Resolution order](#resolution-order).

<div id='installation'></div>

## Installation

```bash
composer require tiny-blocks/environment-variable
```

<div id='how-to-use'></div>

## How to use

### Application environment

Each predicate answers whether the value of the variable is one of the aliases of its environment, regardless of
casing and of surrounding whitespace.

```php
<?php

declare(strict_types=1);

use TinyBlocks\EnvironmentVariable\EnvironmentVariable;

# With APP_ENV=" LocalHost " in the environment.
EnvironmentVariable::from(name: 'APP_ENV')->isLocal();      # true
EnvironmentVariable::from(name: 'APP_ENV')->isProduction(); # false
```

| Case          | Aliases                                       | Predicate       |
|---------------|-----------------------------------------------|-----------------|
| `LOCAL`       | `local`, `localhost`                          | `isLocal`       |
| `TEST`        | `test`, `testing`                             | `isTest`        |
| `DEVELOPMENT` | `development`, `dev`, `develop`               | `isDevelopment` |
| `STAGING`     | `staging`, `stage`, `homolog`, `homologation` | `isStaging`     |
| `PRODUCTION`  | `production`, `prod`                          | `isProduction`  |

A value no environment recognizes, the empty string included, raises `ApplicationEnvironmentUnrecognized` from every
predicate instead of answering `false`. See [FAQ](#faq).

The `ApplicationEnvironment` enum owns the table. Use `fromAlias` when a case is needed, for example to `match` on it,
and `aliases` for the aliases of a case.

```php
<?php

declare(strict_types=1);

use TinyBlocks\EnvironmentVariable\ApplicationEnvironment;

ApplicationEnvironment::fromAlias(alias: 'Homolog'); # ApplicationEnvironment::STAGING
ApplicationEnvironment::STAGING->aliases();          # ['staging', 'stage', 'homolog', 'homologation']
```

### Reading a variable

`from` raises `EnvironmentVariableMissing` when no source holds the variable. `fromOrDefault` carries the default
instead, or an empty string when no default is given.

```php
<?php

declare(strict_types=1);

use TinyBlocks\EnvironmentVariable\EnvironmentVariable;

EnvironmentVariable::from(name: 'MY_VAR');
EnvironmentVariable::fromOrDefault(name: 'MY_VAR', defaultValueIfNotFound: 'default_value');
```

### Conversions

| Method      | Returns  | Accepted values                                                                           |
|-------------|----------|-------------------------------------------------------------------------------------------|
| `toString`  | `string` | Any value, returned unchanged with no trimming.                                           |
| `toInteger` | `int`    | An integer within the `int` range. Values outside it raise instead of being truncated.    |
| `toFloat`   | `float`  | A number with `.` as the decimal separator, no thousands separators.                      |
| `toBoolean` | `bool`   | `1`, `true`, `on`, `yes` for true. `0`, `false`, `off`, `no`, the empty string for false. |

```php
<?php

declare(strict_types=1);

use TinyBlocks\EnvironmentVariable\EnvironmentVariable;

# With PORT=8080 and DEBUG=on in the environment.
EnvironmentVariable::from(name: 'PORT')->toInteger();  # 8080
EnvironmentVariable::from(name: 'DEBUG')->toBoolean(); # true
```

### Checking for a value

`hasValue` reports `false` only for an empty string, a value made of whitespace, and the literal `null` in any casing.
Values like `false`, `0`, and `-1` have a value. Use it before `toBoolean` when a blank variable must not read as
`false`.

```php
EnvironmentVariable::from(name: 'MY_VAR')->hasValue();
```

### Exceptions

Every failure raises a dedicated class from `TinyBlocks\EnvironmentVariable\Exceptions`. All of them extend
`InvalidArgumentException`, so a consumer can catch the broad type or the precise one.

| Exception                            | Raised by                     | Condition                                    |
|--------------------------------------|-------------------------------|----------------------------------------------|
| `EnvironmentVariableMissing`         | `from`                        | No source holds the variable.                |
| `EnvironmentValueNotInteger`         | `toInteger`                   | The value does not represent an integer.     |
| `EnvironmentValueNotFloat`           | `toFloat`                     | The value does not represent a float.        |
| `EnvironmentValueNotBoolean`         | `toBoolean`                   | The value is not a recognized boolean token. |
| `ApplicationEnvironmentUnrecognized` | `is*` predicates, `fromAlias` | No environment recognizes the value.         |

No message carries the value it received, so a failed read on a secret does not leak it into logs or stack traces. The
conversion exceptions name the variable, and `ApplicationEnvironmentUnrecognized` lists the accepted aliases.

### Resolution order

A value is resolved from the first source that holds it. Non-scalar entries in the superglobals are skipped.

| Order | Source              | Read for names in the `HTTP_` namespace         |
|-------|---------------------|-------------------------------------------------|
| 1     | `$_ENV`             | Yes                                             |
| 2     | `$_SERVER`          | No                                              |
| 3     | Process environment | Yes, restricted to the real process environment |

Under CGI-like servers (PHP-FPM, mod_php, FastCGI), request headers are mapped into `$_SERVER` and into the SAPI
environment under an `HTTP_` prefix, so a `Proxy` request header surfaces as `HTTP_PROXY`. For names in that namespace
the library reads only the real process environment, which a request cannot write. See [httpoxy](https://httpoxy.org).

<div id='faq'></div>

## FAQ

### 01. Why does an unrecognized environment raise instead of answering `false`?

Answering `false` turns a typo into the wrong environment. With `APP_ENV=prd`, `isProduction()` answering `false` lets
every branch guarded by it run non-production behavior in production. Raising `ApplicationEnvironmentUnrecognized`
surfaces the misconfiguration at read time, with the accepted aliases in the message.

<div id='license'></div>

## License

Environment variable is licensed under [MIT](LICENSE).

<div id='contributing'></div>

## Contributing

Please follow the [contributing guidelines](https://github.com/tiny-blocks/.github/blob/main/CONTRIBUTING.md) to
contribute to the project.
