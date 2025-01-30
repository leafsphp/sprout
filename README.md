# Leaf Sprout (WIP)

🌱 Fast, lightweight and minimal CLI framework for PHP. It provides a type-safe, feature-rich environment to build CLI apps of all sizes, plus a ton of helpers for building amazing experiences for users interacting with your CLI app.

- Dependency-free, not based on any Laravel or Symfony components
- First-class support for Leaf & Leaf modules
- Comes with built in styling, input handling, output handling, and more
- Support for PHP 7.4 and above
- Easy to use, easy to extend

## Installation

You can install Leaf Sprout using the Leaf CLI or Composer:

```sh
leaf install sprout

# or with composer

composer require leafs/sprout
```

## Usage

To get started, you need to create a file which will be the entry point for your CLI app. This file should be executable and should have a shebang at the top of the file. Here's an example of a simple CLI app:

```php
#!/usr/bin/env php
<?php

use Leaf\Sprout\Command;

require __DIR__ . "/vendor/autoload.php";

$app = sprout()->createApp([
    'name' => 'My CLI App',
    'version' => '1.0.0'
]);

$app->command('greet', function (Command $app) {
    $command->write('Hello, world!');
});

$app->register(\MyCliApp\Commands\GreetCommand::class);

$app->run();
```

You can then run your CLI app using the PHP CLI:

```sh
php my-cli-app greet
```

## Writing Commands

You can write your commands in a functional way using the `command()` method on the app instance, or you can create a class that extends the `Leaf\Sprout\Command` class. Here's an example of a command written as a class:

```php
<?php

namespace MyCliApp\Commands;

use Leaf\Sprout\Command;

class GreetCommand extends Command
{
    protected $signature = 'greet {name}';

    protected $description = 'Greet a user';

    public function handle()
    {
        $this->write("Hello, {$this->argument('name')}!");
    }
}
```

You can then register this command with the app instance:

```php
$app->register(\MyCliApp\Commands\GreetCommand::class);
```

## Styling Output

Leaf Sprout comes with a built-in output styling system that allows you to style your output using a simple API adapted from TermWind. Here's an example of how you can style your output:

```php
$app->command('greet', function (Command $command) {
    $command->write(
        style()->apply('p-4 bg-green-300 text-white')->to('Hello') . ', world!'
    );
});
```

You can also use pre-configured styles like `success()`, `error()`, `warning()`, and `info()`:

```php
style()->success('Operation successful');
style()->error('Operation failed');

...

style()->pill('Operation successful');
style()->pill('Operation failed')->apply('bg-red-500 text-white');

...

style()->italic('This is italic text');
style()->bold('This is bold text');
style()->underline('This is underlined text');
style()->dim('This is dim text');
style()->strikethrough('This is strikethrough text');
```

Most of the styling options are chainable, so you can chain multiple styles together:

```php
style()->bold()->underline()->apply('text-red-500')->to('Hello, world!');
```

## Input Handling

Leaf Sprout comes with a built-in input handling system that allows you to easily get input from the user. The easiest way is to use the `prompt()` method:

```php
$name = sprout()->prompt([
    'type' => 'text', // 'select', 'confirm', 'password', 'number', 'text'
    'initial' => 'John Doe',
    'message' => 'What is your name?',
    'validate' => function ($value) {
        if (empty($value)) {
            return 'Name cannot be empty';
        }

        return true;
    }
]);

$command->write("Hello, $name!"); // Hello, John Doe!
```

You can also have multiple prompts in a single command for more complex interactions like setting up a project via your CLI:

```php
$possiblySetValue = $something ?? null;

$results = sprout()->prompt([
    [
        'type' => 'text',
        'name' => 'name',
        'message' => 'What is your project name?',
        'initial' => 'my-project',
        'validate' => function ($value) {
            if (empty($value)) {
                return 'Name cannot be empty';
            }

            return true;
        }
    ],
    [
        'type' => $possiblySetValue ? null : 'select',
        'name' => 'type',
        'message' => 'What type of project do you want?',
        'initial' => 0,
        'choices' => [
            ['title' => 'Web', 'value' => 'web'],
            ['title' => 'API', 'value' => 'api'],
            ['title' => 'CLI', 'value' => 'cli'],
        ],
    ],
    [
        'type' => 'confirm',
        'name' => 'install',
        'message' => 'Do you want to install dependencies?',
        'initial' => true,
    ],
]);

$results; // ['name' => 'my-project', 'type' => 'web', 'install' => true]
```

When the user runs the command, they will be prompted for the project name, type, and whether they want to install dependencies. The results will be stored in the `$results` variable. The prompt type can be `text`, `select`, `confirm`, `password`, `number`, or `text`, when set to `null`, the prompt will be skipped.

Besides prompts, you can also use the `arguments()` and `options()` methods to get arguments and options passed to the command:

```php
$app->command('greet', function (Command $command) {
    $name = $command->argument('name');
    $uppercase = $command->option('uppercase');

    $greeting = "Hello, $name!";

    if ($uppercase) {
        $greeting = strtoupper($greeting);
    }

    $command->write($greeting);
});
```

## Output Handling

## Process Handling

Sprout comes with a built-in process handling system that allows you to run processes on the system. You can use the `process()` method to run a process:

```php
$process = sprout()->createProcess('ls -la');

$process->onError(function ($error) use ($command) {
    $command->write("An error occurred: $error");
});

$process->run(function ($output) use ($command) {
    $command->write($output);
});

$process->isSuccessful(); // true
$process->getExitCode(); // 0
```

You can also use the `run()` method to run a process and get the output:

```php
$output = sprout()->run('ls -la');

$command->write($output);
```

## Composer & Npm Scripts

A lot of console applications allow you to install composer or npm dependencies, and sprout makes it a whole lot easier to do so:

```php
sprout()->composer()->hasDependency('leafs/fs'); // true
sprout()->composer()->run('composer-script');
sprout()->composer()->install(); // install all dependencies
sprout()->composer()->install('leafs/fs'); // install a package
sprout()->composer()->remove('leafs/fs'); // remove a package

sprout()->npm()->hasDependency('@leafphp/vite'); // true
sprout()->npm()->run('npm-script');
sprout()->npm()->install(); // install all dependencies
sprout()->npm()->install('vite'); // install a package
sprout()->npm()->remove('vite'); // remove a package
```

For npm, you can also specify the package manager you want to use:

```php
sprout()->npm('yarn')->install(); // install all dependencies using yarn
sprout()->npm('pnpm')->install('vite'); // install a package using pnpm
sprout()->npm('bun')->install('vite'); // install a package using bun
```

All the composer and npm commands log the output to the console in real-time. If you want to change the output, you can pass a callback as the second argument:

```php
sprout()->composer()->install(function ($output) {
    $command->write($output);
});

sprout()->npm()->install('vite @leafphp/vite', function ($output) {
    $command->write("NPM -> $output");
});
```

The also return a process instance which you can use to get the output, check if the process was successful, and get the exit code:

```php
$process = sprout()->composer()->install();

$process->isSuccessful(); // true
```
