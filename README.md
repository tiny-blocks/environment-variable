# Environment variable

[![License](https://img.shields.io/badge/license-MIT-green)](https://github.com/tiny-blocks/environment-variable/blob/main/LICENSE)

* [Overview](#overview)
* [Installation](#installation)
* [How to use](#how-to-use)
    + [Creating an environment variable](#creating-an-environment-variable)
    + [Conversions](#conversions)
        - [Convert to string](#convert-to-string)
        - [Convert to integer](#convert-to-integer)
        - [Convert to float](#convert-to-float)
        - [Convert to boolean](#convert-to-boolean)
    + [Check if the environment variable has a value](#check-if-the-environment-variable-has-a-value)
    + [Exceptions](#exceptions)
    + [Resolution order](#resolution-order)
* [FAQ](#faq)
* [License](#license)
* [Contributing](#contributing)

<div id='overview'></div>

## Overview

Provides a type-safe environment variable reader for PHP, wrapping raw values behind a typed accessor with explicit
string, integer, float, and boolean conversion methods. Supports defaults for missing variables and distinguishes
between absent and empty states. Built to surface configuration errors at read time rather than propagate silent
coercions through the system.

Names in the `HTTP_` namespace are read only from sources an HTTP request cannot reach, so a request header is never
mistaken for configuration. See [Resolution order](#resolution-order).

<div id='installation'></div>

## Installation

```bash
composer require tiny-blocks/environment-variable
```

<div id='how-to-use'></div>

## How to use

### Creating an environment variable

To create and work with environment variables, use the `from` method to get an instance of the environment variable.
When no source holds the variable, the method raises `EnvironmentVariableMissing`.

```php
<?php

declare(strict_types=1);

use TinyBlocks\EnvironmentVariable\EnvironmentVariable;

EnvironmentVariable::from(name: 'MY_VAR');
```

To retrieve an environment variable with the option of providing a default value in case the variable does not exist,
use the `fromOrDefault` method.

If the environment variable is not found, the method returns an instance carrying the provided default value instead of
throwing an exception. With no default, the instance carries an empty string.

```php
<?php

declare(strict_types=1);

use TinyBlocks\EnvironmentVariable\EnvironmentVariable;

EnvironmentVariable::fromOrDefault(name: 'MY_VAR', defaultValueIfNotFound: 'default_value');
```

### Conversions

Once you have an instance of the environment variable, you can convert its value into various types.

#### Convert to string

To convert the environment variable to a string. The raw value is returned unchanged, with no trimming.

```php
<?php

declare(strict_types=1);

use TinyBlocks\EnvironmentVariable\EnvironmentVariable;

EnvironmentVariable::from(name: 'MY_VAR')->toString();
```

#### Convert to integer

To convert the environment variable to an integer. Values that do not represent an integer, including values above
`PHP_INT_MAX`, raise `EnvironmentValueNotInteger` instead of being silently truncated.

```php
<?php

declare(strict_types=1);

use TinyBlocks\EnvironmentVariable\EnvironmentVariable;

EnvironmentVariable::from(name: 'MY_VAR')->toInteger();
```

#### Convert to float

To convert the environment variable to a float. The decimal separator is `.` and thousands separators are not accepted.
Values that do not represent a float raise `EnvironmentValueNotFloat`.

```php
<?php

declare(strict_types=1);

use TinyBlocks\EnvironmentVariable\EnvironmentVariable;

EnvironmentVariable::from(name: 'MY_VAR')->toFloat();
```

#### Convert to boolean

To convert the environment variable to a boolean. The accepted values are `1`, `true`, `on`, and `yes` for true, and
`0`, `false`, `off`, `no`, and the empty string for false. Anything else raises `EnvironmentValueNotBoolean`.

```php
<?php

declare(strict_types=1);

use TinyBlocks\EnvironmentVariable\EnvironmentVariable;

EnvironmentVariable::from(name: 'MY_VAR')->toBoolean();
```

### Check if the environment variable has a value

Checks if the environment variable has a value. Values like `false`, `0`, and `-1` are valid and non-empty. Only an
empty string, a value made of whitespace, and the literal `null` in any casing report no value.

```php
<?php

declare(strict_types=1);

use TinyBlocks\EnvironmentVariable\EnvironmentVariable;

EnvironmentVariable::from(name: 'MY_VAR')->hasValue();
```

### Exceptions

Every failure raises a dedicated class from `TinyBlocks\EnvironmentVariable\Exceptions`. All of them extend
`InvalidArgumentException`, so a consumer can catch the broad type or the precise one.

| Exception                    | Raised by   | Condition                                              |
|------------------------------|-------------|--------------------------------------------------------|
| `EnvironmentVariableMissing` | `from`      | No source holds the variable.                          |
| `EnvironmentValueNotInteger` | `toInteger` | The value does not represent an integer.               |
| `EnvironmentValueNotFloat`   | `toFloat`   | The value does not represent a float.                  |
| `EnvironmentValueNotBoolean` | `toBoolean` | The value is not one of the recognized boolean tokens. |

Messages carry the variable name, never its value, so a failed conversion on a secret does not leak it into logs or
stack traces.

### Resolution order

A value is resolved from the first source that holds it. Non-scalar entries in the superglobals are skipped, so an array
left in `$_ENV` never reaches a conversion method.

| Order | Source              | Read for names in the `HTTP_` namespace         |
|-------|---------------------|-------------------------------------------------|
| 1     | `$_ENV`             | Yes                                             |
| 2     | `$_SERVER`          | No                                              |
| 3     | Process environment | Yes, restricted to the real process environment |

Under CGI-like servers (PHP-FPM, mod_php, FastCGI), request headers are mapped into `$_SERVER` and into the SAPI
environment under an `HTTP_` prefix, so a `Proxy` request header surfaces as `HTTP_PROXY`. For names in that namespace
the library skips `$_SERVER` and reads only the real process environment, which a request cannot write. A variable
genuinely set in the environment still resolves. See [httpoxy](https://httpoxy.org) for the background.

<div id='faq'></div>

## FAQ

### 01. Why is a name starting with `HTTP_` not read from `$_SERVER`?

Because anyone sending a request can write that namespace. Only the request-supplied value is refused, so a variable
genuinely set in the process environment still resolves. See [Resolution order](#resolution-order).

### 02. Why does `toBoolean` return `false` for an empty value instead of raising?

The empty string is one of the values PHP's boolean filter recognizes as false, alongside `0`, `off`, and `no`. A
variable declared but left blank therefore reads as `false`. Use `hasValue` first when the difference between blank and
false matters.

<div id='license'></div>

## License

Environment variable is licensed under [MIT](LICENSE).

<div id='contributing'></div>

## Contributing

Please follow the [contributing guidelines](https://github.com/tiny-blocks/tiny-blocks/blob/main/CONTRIBUTING.md) to
contribute to the project.
