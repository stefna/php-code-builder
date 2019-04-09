# Php code builder

## Installation

    $ composer require stefna/php-code-builder

## API

### PhpFile

The actual file to save.

#### Methods

##### `setStrict()`

Mark files with `declare(strict_types=1);`

##### `setNamespace(string $ns)`

Set file namespace

##### `setSource(string $code)`

Set raw php code to be included in file

Like 
```php
$file = new PhpFile();
$file->setSource("spl_autoload_register('$autoloaderName');");
```

##### `addFunction(PhpFunction $func)`

Add function to file. Files can contain multiple functions

##### `addClass(PhpClass $class)`

Add class to file. Files can contain multiple classes

##### `addTrait(PhpTrait $trait)`

Add trait to file. Files can contain multiple traits

##### `addInterface(PhpInterface $interface)`

Add interface to file. Files can contain multiple interfaces

### PhpParam

Used to make complex parameter arguments

#### Usage

```php
new PhpParam('string', 'test') => string $test
new PhpParam('object', 'test', null) => object $test = null

$param = new PhpParam('DateTimeInterface', 'date');
$param->allowNull(true);
$param->getSource() => ?DateTimeInterface $date

$param = new PhpParam('float', 'price', 1.5);
$param->allowNull(true);
$param->getSource() => ?float $price = 1.5
```


### PhpFunction

#### Usage

`__construct(string $identifier, array $params, string $source, ?PhpDocComment $comment = null, ?string $returnTypeHint = null)`

##### Example
```php
$func = new PhpFunction(
    'testFunc',
    [
        'param1', // Simple parameter
        new PhpParam('int', 'param2', 1),
    ],
    "return $param1 + $param2",
    null, // no docblock
    'int'
);

echo $func->getSource();

```

##### Output:

```php
function testFunc($param1, int $param2 = 1): int
{
    return $param1 + $param2;
}
```

### PhpMethod

Method is an extension of `PhpFunction` with support for accessors like `public`, `protected`, `private`, `final`, `static` and `abstract`

#### Usage

With return typehint
```php
$method = new PhpMethod('private', 'test', [], 'return 1', null, 'int');
echo $method->getSource();

-------

private function test(): int
{
    return 1;
}
```

With docblock
```php
$method = new PhpMethod(
    'private',
    'test',
    [],
    'return 1',
    new PhpDocComment('Test Description')
);
echo $method->getSource();

-------
/**
 * Test Description
 */
private function test()
{
    return 1;
}
```
Static method
```php
$method = new PhpMethod('protected', 'test', [], 'return 1');
$method->setStatic();
echo $method->getSource();

-------

protected static function test()
{
    return 1;
}
```

Final method
```php
$method = new PhpMethod('public', 'test', [], 'return 1');
$method->setFinal();
echo $method->getSource();

-------

final public function test()
{
    return 1;
}
```
Abstract method
```php
$method = new PhpMethod('public', 'test', [], 'return 1', null, 'int');
$method->setAbstract();
echo $method->getSource();

-------

abstract public function test(): int;
```

