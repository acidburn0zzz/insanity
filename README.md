# InSanity

[![License](https://img.shields.io/badge/License-zlib/libpng-blue.svg)](https://github.com/Lusito/insanity/blob/master/LICENSE)


|Master|[![Build Status](https://travis-ci.org/Lusito/insanity.svg?branch=master)](https://travis-ci.org/Lusito/insanity)|[![Code Coverage](https://coveralls.io/repos/github/Lusito/insanity/badge.svg?branch=master)](https://coveralls.io/github/Lusito/insanity)|
|---|---|---|
|Develop|[![Build Status](https://travis-ci.org/Lusito/insanity.svg?branch=develop)](https://travis-ci.org/Lusito/insanity)|[![Code Coverage](https://coveralls.io/repos/github/Lusito/insanity/badge.svg?branch=develop)](https://coveralls.io/github/Lusito/insanity)|

Extensible validation and sanitization library for various input sources like `$_POST` and routing parameters.

### Why InSanity?

- Supports validation of various input sources (per field configurable)
- Extensible
- Translatable
- Supports global php functions like `trim()`, `intval()`, etc.
- Supports array field inputs
- No dependencies
- Automated tests with 100% code coverage
- Liberal license: [zlib/libpng](https://github.com/Lusito/insanity/blob/master/LICENSE)

**Note**: This has just been released, so it might not be perfect yet. Feel free to report issues, improvement ideas or create pull requests.

### Requirements
- PHP >= 7.1

### Installation

Install via composer:

```composer require lusito/insanity```

Include the autoloader in your php script, unless you've done that already:

```php
require __DIR__ . '/vendor/autoload.php';
```

### Usage 

An example use-case:
```php

use Lusito\InSanity\InSanity;

//...

$in = new InSanity(); // or new InSanity($_GET) if you want to use $_GET as default instead of $_POST.
$siteId = $in->site_id('Site ID', $_GET)->required->is_natural->intval; // use $_GET as input instead of $_POST for this field
$parentId = $in->parent_id('Parent ID')->required->is_natural->intval;
$title = $in->title('Title')->required->trim->min_length(2)->max_length(255)->val;
$slug = $in->slug('Slug')->max_length(255)->val;
$visible = $in->visible('Visible')->required->is_bool->boolval;

$errors = $in->getErrors();
if ($errors) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => "Invalid input!", 'validation_errors' => $errors], JSON_UNESCAPED_SLASHES);
} else {
    // instead of storing the results manually like above, you can just fetch an associative array like this:
    $json = $in->toJSON();
}
```

### The classes

This library consists of these classes:
- `InSanity`: This coordinates the validation process.
- `Field`: Used to chain rules and sanitizers to process values. Instances are automatically created.
- `RuleHandler`: A class that contains the included rules.
- `ErrorHandler`: Aggregates errors.
- `Translator`: A class used to translate messages and field names.

The last 3 classes can be extended or even replaced with your own implementations if you feel like it.

Details follow:

#### InSanity

This class receives 3 parameters:

- An optional (associative) array that serves as the default input if none was specified. If this is `null`, `$_POST` will be used.
- An optional instance of `RuleHandler` (or a replacement class). If `null`, a default `RuleHandler` is created.
- An optional instance of `ErrorHandler` (or a replacement class). If `null`, a default `ErrorHandler` is created.

To start validation and sanitization on a field, call it like this:

```php
$in = new InSanity();
$in->my_field_name('My Field Label')->required->trim->min_length(2);
```

This consists of 2 parts:
- `$in->my_field_name('My Field Label')`
  - `my_field_name` is the field name as it exists in the input array.
  - `My Field Label` is the text that will be used when generating error messages.
  - This returns an instance of `Field`.
- `->required->trim->min_length(2)`
  - These apply your rules. Rule calls return the `Field` instance for easy chaining.

You can get errors using `$in->getErrors()`. If you need to access a field afterwards (or apply an additional later), you can access the fields later by using `$in->my_field_name`.

For convenience, a method `$in->toJSON()` exists, which will create an associative array field_name => field_value.

#### Field

Field instances are created by `InSanity` (see above). The instance supports executing and chaining rules.

A rule can be run in 3 different ways:

```php
// shortest way:
$field->required;
// does the same as:
$field->required();
// A parameter can be supplied like this:
$field->min_length(2);
```

The rule (and filter) methods are applied in order. If the given method returns a boolean value, it's interpreted as a rule, otherwise as a filter (the sole exception is `boolval`, see below). 

Examples:

- A return value of false will fail the validation (and skip the rest of the rules)
- A value of true will continue with the next rule (or filter).
- Anything else will change the current value to what has been returned by the rule handler.

Some methods exist in addition to normal rule handling:

- `default(default)`: If value is `null`, value will be set to `default`.
- `boolval()`: This was added for two reasons:
  - `boolval` from PHP doesn't work on the same values as the is_bool rule.
  - it returns true or false, which would normally be interpreted as a validation result and thus, not be stored.
- `getValue()`: Gets the current value of the field (after rules have been applied)
- `getError()`: Gets the error message, or `null` if no error has been set yet.
- `setFailed(rule, param=null)`: Sets the field to failed. An error message will be generated using the translator class.

#### RuleHandler

As said, RuleHandler contains validation methods.
That method receives 1-2 parameters. The first being the value to validate and the second is the parameter passed by the user (`null` if none given).

The following rules are included in the built-in RuleHandler class:
- `is_alpha` (a-z)
- `is_alnum` (a-z and 0-9)
- `is_alnum_dash` (a-z, 0-9, _ and -)
- `is_numeric` (a numeric value, prefixed by a + or a -)
- `is_integer` (an integer value, prefixed by a + or a -)
- `is_natural` (an integer value without prefix)
- `is_natural` (an integer value without prefix and above zero)
- `is_bool` (true|false|on|off|yes|no|1|0)
- `valid_email` (a valid e-mail address)
- `required` (the trimmed value may not be empty)
- `min_length(length)` (the value must have a minimum length)
- `max_length(length)` (the value must have a maximum length)
- `exact_length(length)` (the value must have a specified length)


#### Translator

If you plan on writing your own rules or if you need more than english as target language, you can either add/override translations by supplying an associative array to the constructor or you can write your own implementation.

#### ErrorHandler

The ErrorHandler builds and aggregates error messages. It will create a new `Translator` instance when needed. If you want it to use a custom `Translator` instance, either pass the instance as first argument to the constructor, or override the `getTranslator()` method.

### Report isssues

Something not working quite as expected? Do you need a feature that has not been implemented yet? Check the [issue tracker](https://github.com/Lusito/insanity/issues) and add a new one if your problem is not already listed. Please try to provide a detailed description of your problem, including the steps to reproduce it.

### Contribute

Awesome! If you would like to contribute with a new feature or submit a bugfix, fork this repo and send a pull request. Please, make sure all the unit tests are passing before submitting and add new ones in case you introduced new features.

### License

InSanity has been released under the [zlib/libpng](https://github.com/Lusito/insanity/blob/master/LICENSE) license, meaning you
can use it free of charge, without strings attached in commercial and non-commercial projects. Credits are appreciated but not mandatory.
