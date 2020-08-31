# roolith-generator
Generate php file using php

### install
```shell script
composer require roolith/generator
```

### usage
After install generator via composer, create a file `index.php` and add following code - 

```php
<?php
use Roolith\Generator\GeneratorFactory;

require_once __DIR__ . '/PATH_TO_YOUR_VENDOR/vendor/autoload.php';

$generator = GeneratorFactory::getInstance();
$generator
    ->setTemplateDirectory(__DIR__.'/template') // this is your template directory
    ->setProjectBaseDirectory(__DIR__) // this is project base directory
    ->watch($argv); // this $argv to get console arguments

```

Now create a folder on project root called `template` and inside `template` folder create a file called `controller.txt` and add following code - 

```text
# outputBaseDir: Controllers
<?php
namespace Something;

class {{name}}Controller extends Controller
{
    public function index()
    {
    }

    public function create()
    {
    }
}

```

`outputBaseDir` means - in which folder file will be generated.\
`{{name}}` means command name argument.

Now run following command 

```shell script
php index.php generate controller Demo
```

It should create a `DemoController.php` inside `Controllers` folder. If you want to add another template then add test.txt and add your template code and run following command - 

```shell script
php index.php generate test something
```

### add custom command 

```shell script
php index.php test
```

To make this work, create a `TestCommand.php` class and it should look like following - 

```php
<?php
use Roolith\Generator\Command;
use Roolith\Generator\Console;
use Roolith\Generator\FileGenerator;
use Roolith\Generator\FileParser;
use Roolith\Generator\Interfaces\CommandInterface;

class TestCommand implements CommandInterface
{
    public function register()
    {
        return [
            'name' => 'test',
            'alias' => [],
            'typeAlias' => [],
        ];
    }

    public function handle(Command $command, Console $console, FileParser $fileParser, FileGenerator $fileGenerator)
    {
        $console->output('Test command registered!');
    }
}
```

Then register your command with generator class - 

```php
$generator
    ->setTemplateDirectory(__DIR__.'/template')
    ->setProjectBaseDirectory(__DIR__)
    ->registerCommandClass([
        TestCommand::class
    ])
    ->watch($argv);
```

There is a `demo` folder added for more details. 

### test cases

```shell script
$ ./vendor/bin/phpunit --testdox tests
PHPUnit 9.2.6 by Sebastian Bergmann and contributors.

Command
 ✔ Should get argument name
 ✔ Should get argument type
 ✔ Should get argument value
 ✔ Should get argument type with alias
 ✔ Should get registered command by name

Console
 ✔ Should set arguments
 ✔ Should remove first item from argument
 ✔ Should return bool for has arguments

File Generator
 ✔ Should set file extension
 ✔ Should set project directory
 ✔ Should save file

File Parser
 ✔ Should add default instructions
 ✔ Should set file extension
 ✔ Should set directory
 ✔ Should check whether template exists or not
 ✔ Should parse template

Generate Factory
 ✔ Should have get instance method

Generator
 ✔ Should create instance
 ✔ Should set template directory
 ✔ Should set project base directory
 ✔ Should have watch method
 ✔ Should register custom command

Time: 00:00.012, Memory: 6.00 MB

OK (22 tests, 28 assertions)
```
