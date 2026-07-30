<?php

declare(strict_types=1);

use Leaf\Sprout\App;
use Leaf\Sprout\Command;

/*
|--------------------------------------------------------------------------
| Test fixtures
|--------------------------------------------------------------------------
*/

class GreetCommand extends Command
{
    protected $signature = 'greet
        {name : The name of the person}
        {--greeting=Hello : The greeting to use}';
    protected $description = 'Greet someone properly';

    protected function handle(): int
    {
        $this->info("{$this->option('greeting')}, {$this->argument('name')}!");

        return 0;
    }
}

class DashedCommand extends Command
{
    // https://github.com/leafsphp/leaf/issues/329 — dashes in command names
    protected $signature = 'auto-create-reports
        {name? : Report name}
        {--dry-run : Preview without writing}';
    protected $description = 'Command with dashes, like Aloe allowed';

    protected function handle(): int
    {
        $this->info('report: ' . ($this->argument('name') ?? 'default') . ($this->option('dry-run') ? ' (dry)' : ''));

        return 0;
    }
}

class NamespacedDashCommand extends Command
{
    protected $signature = 'reports:auto-create';
    protected $description = 'Dashes and namespaces together';

    protected function handle(): int
    {
        $this->info('namespaced dash ok');

        return 0;
    }
}

class OptionalArgCommand extends Command
{
    protected $signature = 'shout {name?}';
    protected $description = 'Shout at someone, or no one';

    protected function handle(): int
    {
        $this->write('shouting at: ' . ($this->argument('name') ?? 'nobody'));

        return 0;
    }
}

class ArrayArgCommand extends Command
{
    protected $signature = 'invite {names* : People to invite}';
    protected $description = 'Invite people';

    protected function handle(): int
    {
        $names = $this->argument('names');
        $this->write('invited: ' . join('+', is_array($names) ? $names : [$names]));

        return 0;
    }
}

class RequiredOptionCommand extends Command
{
    protected $signature = 'deploy {--token= : The deploy token}';
    protected $description = 'Deploy the app';

    protected function handle(): int
    {
        $this->write('token: ' . $this->option('token'));

        return 0;
    }
}

class ShortOptionCommand extends Command
{
    protected $signature = 'clean {--f|force : Skip confirmation}';
    protected $description = 'Clean things';

    protected function handle(): int
    {
        $this->write($this->option('force') ? 'forced' : 'gentle');

        return 0;
    }
}

class FailingCommand extends Command
{
    protected $signature = 'fail:hard';
    protected $description = 'Always fails with exit code 3';

    protected function handle(): int
    {
        $this->error('it broke');

        return 3;
    }
}

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function sproutApp(array $commands = [], array $config = []): App
{
    $app = sprout()->createApp(array_merge([
        'name' => 'TestApp',
        'version' => 'v0.0-test',
    ], $config));

    if (!empty($commands)) {
        $app->register($commands);
    }

    return $app;
}

/**
 * Run an app in-process with a fake argv, capturing output + exit code.
 */
function runSprout(App $app, array $argv): array
{
    $_SERVER['argv'] = array_merge(['sprout-test'], $argv);

    ob_start();
    $code = $app->run(false);
    $output = ob_get_clean();

    return ['code' => $code, 'output' => stripAnsi($output)];
}

function stripAnsi(string $text): string
{
    return preg_replace('/\033\[[0-9;]*[A-Za-z]/', '', $text);
}

/**
 * Run a real php script in a sandbox dir, returning exit code + output.
 * Used for binary-level checks (exit() propagation, piped prompts).
 */
function execSprout(string $scriptBody, string $args = '', ?string $stdin = null): array
{
    $dir = sys_get_temp_dir() . '/sprout-test-' . uniqid();
    mkdir($dir, 0777, true);

    $autoload = realpath(__DIR__ . '/../vendor/autoload.php');
    $script = "<?php\nrequire '$autoload';\n" . $scriptBody;
    file_put_contents("$dir/app.php", $script);

    $php = escapeshellarg(PHP_BINARY);
    $cmd = "$php $dir/app.php $args";

    if ($stdin !== null) {
        $cmd = 'printf ' . escapeshellarg($stdin) . " | $cmd";
    }

    exec("cd $dir && $cmd 2>/dev/null", $outputLines, $code);

    return ['code' => $code, 'output' => stripAnsi(join("\n", $outputLines))];
}
