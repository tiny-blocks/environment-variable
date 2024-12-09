# Environment variable

[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

* [Overview](#overview)
* [Installation](#installation)
* [How to use](#how-to-use)
* [License](#license)
* [Contributing](#contributing)

<div id='overview'></div> 

## Overview

Provides a simple and flexible solution for managing environment variables, with easy access, type conversions, and
validation handling.

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
EnvironmentVariable::from(name: 'MY_VAR');
```

### Conversions

Once you have an instance of the environment variable, you can convert its value into various types.

#### Convert to string

To convert the environment variable to a string.

```php
$environmentVariable = EnvironmentVariable::from(name: 'MY_VAR');
$environmentVariable->toString();
```

#### Convert to integer

To convert the environment variable to an integer.

```php
$environmentVariable = EnvironmentVariable::from(name: 'MY_VAR');
$environmentVariable->toInteger();
```

#### Convert to boolean

To convert the environment variable to a boolean.

```php
$environmentVariable = EnvironmentVariable::from(name: 'MY_VAR');
$environmentVariable->toBoolean();
```

### Check if the environment variable has a value

Checks if the environment variable has a value. Values like `false`, `0`, and `-1` are valid and non-empty.

```php
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
