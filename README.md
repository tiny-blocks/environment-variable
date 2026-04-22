# Environment variable

[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

* [Overview](#overview)
* [Installation](#installation)
* [How to use](#how-to-use)
* [License](#license)
* [Contributing](#contributing)

<div id='overview'></div>

## Overview

Provides a type-safe environment variable reader for PHP, wrapping raw values behind a typed accessor with explicit
string, integer, and boolean conversion methods. Supports defaults for missing variables and distinguishes between
absent and empty states. Built to surface configuration errors at read time rather than propagate silent coercions
through the system.

<div id='installation'></div>

## Installation

```bash
composer require tiny-blocks/environment-variable
```

<div id='how-to-use'></div>

## How to use

### Creating an environment variable

To create and work with environment variables, use the `from` method to get an instance of the environment variable.

```php
use TinyBlocks\EnvironmentVariable\EnvironmentVariable;

EnvironmentVariable::from(name: 'MY_VAR');
```

To retrieve an environment variable with the option of providing a default value in case the variable does not exist,
use the `fromOrDefault` method.

If the environment variable is not found, the method returns an instance carrying the provided default value instead
of throwing an exception.

```php
use TinyBlocks\EnvironmentVariable\EnvironmentVariable;

EnvironmentVariable::fromOrDefault(name: 'MY_VAR', defaultValueIfNotFound: 'default_value');
```

### Conversions

Once you have an instance of the environment variable, you can convert its value into various types.

#### Convert to string

To convert the environment variable to a string.

```php
use TinyBlocks\EnvironmentVariable\EnvironmentVariable;

$environmentVariable = EnvironmentVariable::from(name: 'MY_VAR');
$environmentVariable->toString();
```

#### Convert to integer

To convert the environment variable to an integer.

```php
use TinyBlocks\EnvironmentVariable\EnvironmentVariable;

$environmentVariable = EnvironmentVariable::from(name: 'MY_VAR');
$environmentVariable->toInteger();
```

#### Convert to boolean

To convert the environment variable to a boolean.

```php
use TinyBlocks\EnvironmentVariable\EnvironmentVariable;

$environmentVariable = EnvironmentVariable::from(name: 'MY_VAR');
$environmentVariable->toBoolean();
```

### Check if the environment variable has a value

Checks if the environment variable has a value. Values like `false`, `0`, and `-1` are valid and non-empty.

```php
use TinyBlocks\EnvironmentVariable\EnvironmentVariable;

$environmentVariable = EnvironmentVariable::from(name: 'MY_VAR');
$environmentVariable->hasValue();
```

<div id='license'></div>

## License

Environment variable is licensed under [MIT](LICENSE).

<div id='contributing'></div>

## Contributing

Please follow the [contributing guidelines](https://github.com/tiny-blocks/tiny-blocks/blob/main/CONTRIBUTING.md) to
contribute to the project.
