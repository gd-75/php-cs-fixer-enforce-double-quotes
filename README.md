# php-cs-fixer-enforce-double-quotes
A rule for PHP CS Fixer to enforce double quotes.

## Installation and usage
```shell
composer require --dev gd-75/php-cs-fixer-enforce-double-quotes
```

In your PHP CS Fixer configuration, add:
```php
use GD75\DoubleQuoteFixer\DoubleQuoteFixer;
use PhpCsFixer\Config;

$config = new Config();

$config
    ->registerCustomFixers(
        [
            new DoubleQuoteFixer()
        ]
    )
    ->setRules(
        [
            "GD75/double_quote_fixer" => true,
        ]
    );

```
## Examples of strings that will be fixed and string that will be ignored
### Will be ignored
- `'He was saying "Don't you think that is weird?"'` => Keeps single quotes to avoid escaping inside the string
- `'Use \p to introduce a magic character'` => `\p` would be interpreted as an escape sequence
### Will be transformed
- `'Hello Mr World'` => Will be converted to `"Hello Mr World"`

> ### Warning
> Before auto-fixing quotes on your project, please run a dry-run with diffs to make sure the fixer works correctly.

## TO-DO
- Add tests
- Maybe rework the fix method to support more complex strings and improve general efficiency
- Create a configuration for the allowance of single quoted strings when it contains a double quote