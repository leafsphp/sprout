<?php

declare(strict_types=1);

namespace Leaf\Sprout;

class Prompt
{
    protected $questions = [];
    protected $answers = [];

    protected $currentInput = '';

    protected $cursor = 0;

    protected $currentSelection = 0;

    protected $screenLines = 0;

	protected $isWindows = false;

    public function __construct(array $prompt)
    {
        $this->questions = $prompt;
		$this->isWindows = PHP_OS_FAMILY === 'Windows';
    }

    public function onError()
    {
        //
    }

    public function ask(): array
    {
        foreach ($this->questions as $key => $prompt) {
            if (is_callable($prompt['type'])) {
                $prompt['type'] = $prompt['type']($this->answers);
            }

            if ($prompt['type'] === null) {
                $this->cursor++;
                continue;
            }

            $this->questions[$this->cursor]['cursor'] = $key;

            $this->renderPrompt($prompt);

            $this->getAnswer($prompt);

            $answer = $this->answers[$key] ?? null;

            if ($answer === null) {
                $this->onError();
                return [];
            }

            $this->answers[$prompt['name']] = $answer;
        }

        return $this->answers;
    }

    protected function renderPrompt($prompt, $rerender = true)
    {
        if (is_callable($prompt['type'])) {
            $prompt['type'] = $prompt['type']($this->answers);
        }

        if ($prompt['type'] === 'select') {
            $this->renderSelectPrompt($prompt, $rerender);
            return;
        }

        if ($prompt['type'] === 'confirm') {
            $this->renderConfirmPrompt($prompt, $rerender);
            return;
        }

        $this->renderTextPrompt($prompt, $rerender);
    }

    protected function getAnswer($prompt)
    {
        $stdin = fopen('php://stdin', 'r');

        if (!$this->isWindows) {
            stream_set_blocking($stdin, false);
            system('stty cbreak -echo');
        }

        while (1) {
            $keyPress = $this->isWindows ? $this->readWindowsInput() : fgets($stdin);

            if ($keyPress) {
                if ($keyPress === "\n" || $keyPress === "\r") {
                    if ($prompt['type'] === 'select') {
                        $this->answers[$this->cursor] = $prompt['choices'][$this->currentSelection]['value'];
                        $this->currentSelection = 0;
                    } elseif ($prompt['type'] === 'text') {
                        if ($this->currentInput === '') {
                            $this->currentInput = $prompt['default'] ?? '';
                        }

                        $this->answers[$this->cursor] = $this->currentInput;
                        $this->currentInput = '';
                    } elseif ($prompt['type'] === 'confirm') {
                        $this->answers[$this->cursor] = $prompt['default'] ?? false;
                        $this->currentInput = '';
                    }

                    $this->questions[$this->cursor]['answered'] = true;
                    $this->renderPrompt($this->questions[$this->cursor], $prompt['type'] === 'select');
                    $this->cursor++;

                    break;
                } elseif ($keyPress === "\t") {
                    continue;
                } elseif ($keyPress === "\033[A" || $keyPress === "UP") {
                    if ($prompt['type'] === 'select') {
                        $this->currentSelection = $this->currentSelection === 0 ? count($prompt['choices']) - 1 : $this->currentSelection - 1;
                        $this->renderPrompt($this->questions[$this->cursor], false);
                    }
                } elseif ($keyPress === "\033[B" || $keyPress === "DOWN") {
                    if ($prompt['type'] === 'select') {
                        $this->currentSelection = $this->currentSelection === count($prompt['choices']) - 1 ? 0 : $this->currentSelection + 1;
                        $this->renderPrompt($this->questions[$this->cursor], false);
                    }
                } elseif ($keyPress === "\033[C") {
                    // right
                } elseif ($keyPress === "\033[D") {
                    // left
                } elseif ($keyPress === "\177" || $keyPress === "BACKSPACE") {
                    if ($this->currentInput !== '') {
                        $this->currentInput = substr($this->currentInput, 0, -1);
                        $this->renderPrompt($this->questions[$this->cursor], $this->currentInput !== '');
                    }
                } else {
                    if ($prompt['type'] === 'confirm') {
                        $keyPress = strtolower($keyPress);
                        $keyPress = $keyPress === 'y' ? true : ($keyPress === 'n' ? false : '');

                        if ($keyPress !== '') {
                            $this->answers[$this->cursor] = $keyPress;
                            $this->questions[$this->cursor]['answered'] = true;
                            $this->renderPrompt($this->questions[$this->cursor]);
                            $this->cursor++;
                            break;
                        }

                        continue;
                    }

                    $this->currentInput .= $keyPress;
                    $this->renderPrompt($this->questions[$this->cursor]);
                }
            }
        }

        fclose($stdin);

        if (!$this->isWindows) {
            system('stty sane');
        }
    }

    protected function renderTextPrompt($prompt, $rerender = true)
    {
        if ($this->currentInput || !$rerender) {
            echo "\033[1A";
            echo "\033[K";
        }

        if ($prompt['answered'] ?? false) {
            sprout()->style()->write(
                "\033[32m✔\033[0m \033[1;1m{$prompt['message']}:\033[0m \033[90m…\033[0m {$this->answers[$prompt['cursor']]}" . PHP_EOL
            );

            return;
        }

        $output = $this->currentInput ?: "\033[90m{$prompt['default']}\033[0m";

        sprout()->style()->write(
            "\033[36m?\033[0m \033[1;1m{$prompt['message']}:\033[0m \033[90m›\033[0m $output" . PHP_EOL
        );
    }

    protected function renderConfirmPrompt($prompt, $rerender = true)
    {
        if ($this->currentInput || !$rerender) {
            echo "\033[1A";
            echo "\033[K";
        }

        if ($prompt['answered'] ?? false) {
            echo "\033[1A\033[J";

            $parsedAnswer = $this->answers[$prompt['cursor']] ? 'Yes' : 'No';

            sprout()->style()->write(
                "\033[32m✔\033[0m \033[1;1m{$prompt['message']}:\033[0m \033[90m…\033[0m $parsedAnswer" . PHP_EOL
            );

            return;
        }

        sprout()->style()->write(
            "\033[36m?\033[0m \033[1;1m{$prompt['message']}:\033[0m \033[90m›\033[0m \033[90m(y/N)\033[0m" . PHP_EOL
        );
    }

    protected function renderSelectPrompt($prompt, $rerender = true)
    {
        $move = count($prompt['choices']) + 1;

        if ($this->currentSelection || !$rerender) {
            echo "\033[{$move}A";
            echo "\033[K";
        }

        if ($prompt['answered'] ?? false) {
            echo "\033[{$move}A\033[J";

            sprout()->style()->write(
                "\033[32m✔\033[0m \033[1;1m{$prompt['message']}:\033[0m › … {$this->answers[$prompt['cursor']]}" . PHP_EOL
            );

            return;
        }

        sprout()->style()->writeln(
            "\033[36m?\033[0m \033[1;1m{$prompt['message']}:\033[0m \033[90m› - Use arrow-keys. Return to submit.\033[0m"
        );

        foreach ($prompt['choices'] as $key => $option) {
            $selected = $this->currentSelection === $key ? "\033[36m❯\033[0m" : ' ';
            $item = $this->currentSelection === $key ? "\033[4;1m{$option['title']}\033[0m" : $option['title'];

            sprout()->style()->writeln(
                "$selected  $item"
            );
        }
    }

    protected function clearScreen()
    {
        echo "\033c"; // ANSI escape code to clear screen
    }

    protected function readWindowsInput(): string
    {
        // Windows does not support raw stdin reading like Unix, so we use `readline()`
        $char = trim(readline());

        if ($char === "") {
            return "\n"; // Simulate Enter key
        }

        if ($char === "\x08") { // Backspace
            return "BACKSPACE";
        }

        if ($char === "\xe0") { // Special key indicator (arrows)
            $arrow = fread(STDIN, 1);
            return match ($arrow) {
                "H" => "UP",
                "P" => "DOWN",
                default => "",
            };
        }

        return $char;
    }

    protected function readKey()
    {
        system("stty -echo"); // Disable terminal echo
        system("stty cbreak"); // Disable line buffering
        $key = fread(STDIN, 1);
        system("stty echo"); // Re-enable terminal echo
        system("stty -cbreak"); // Restore line buffering
        return $key;
    }
}
