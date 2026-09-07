# roolith-generator
Generate php file using php

### Install
```shell
composer require roolith/generator
```

### Setup
Create `index.php`:
```php
<?php
use Roolith\Generator\GeneratorFactory;

require_once __DIR__ . '/vendor/autoload.php';

$generator = GeneratorFactory::getInstance();
$generator
    ->setTemplateDirectory(__DIR__.'/template')
    ->setProjectBaseDirectory(__DIR__)
    ->watch($argv);
```

### Add a template
Create `template/controller.txt`:
```text
# outputBaseDir: Controllers
<?php
namespace Something;

class {{name}} extends Controller
{
}
```

* First line sets output folder.
* `{{name}}` is replaced with name in title case, `{name}` keeps raw value.

Generate:
```shell
php index.php generate controller DemoController
php index.php g c DemoController
```

This creates `Controllers/DemoController.php`. To add another type, just add another template file. Example `template/model.txt`:
```text
# outputBaseDir: Models
<?php
namespace Something;

class {{name}} extends Model
{
    protected $table = '';
}
```
Then run:
```shell
php index.php generate model User
```
This creates `Models/User.php`.

Shortcuts: `generate` = `g`, `controller` = `c`, `command` = `cmd`.

### Add a custom command
Run with `php index.php test`. Create `TestCommand.php`:
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
        return ['name' => 'test', 'alias' => [], 'typeAlias' => []];
    }

    public function handle(Command $command, Console $console, FileParser $fileParser, FileGenerator $fileGenerator)
    {
        $console->output('Test command registered!');
    }
}
```

Register it:
```php
$generator
    ->setTemplateDirectory(__DIR__.'/template')
    ->setProjectBaseDirectory(__DIR__)
    ->registerCommandClass([TestCommand::class])
    ->watch($argv);
```

See `demo` folder for full example.

### Tests
```shell
./vendor/bin/phpunit --testdox tests
```
