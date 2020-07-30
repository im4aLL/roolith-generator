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
use Roolith\GeneratorFactory;

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
use Roolith\Command;
use Roolith\Console;
use Roolith\FileGenerator;
use Roolith\FileParser;
use Roolith\Interfaces\CommandInterface;

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

