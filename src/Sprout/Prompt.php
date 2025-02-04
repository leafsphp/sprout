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

    public function __construct(array $prompt)
    {
        $this->questions = $prompt;
    }

    public function onError()
    {
        // 
    }

    public function ask(): array
    {
        foreach ($this->questions as $key => $prompt) {
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

    protected function renderPrompt($prompt)
    {
        if ($prompt['type'] === 'select') {
            $this->renderSelectPrompt($prompt);
            return;
        }

        $this->renderTextPrompt($prompt);
    }

    protected function getAnswer($prompt)
    {
        $stdin = fopen('php://stdin', 'r');
        stream_set_blocking($stdin, false);
        system('stty cbreak -echo');

        while (1) {
            $keyPress = fgets($stdin);

            if ($keyPress) {
                if ($keyPress === "\n") {
                    if ($prompt['type'] === 'select') {
                        $this->answers[$this->cursor] = $prompt['choices'][$this->currentSelection]['value'];
                        $this->currentSelection = 0;
                    } elseif ($prompt['type'] === 'text') {
                        if ($this->currentInput === '') {
                            $this->currentInput = $prompt['default'] ?? '';
                        }

                        $this->answers[$this->cursor] = $this->currentInput;
                        $this->currentInput = '';
                    }

                    $this->questions[$this->cursor]['answered'] = true;
                    $this->renderPrompt($this->questions[$this->cursor]);
                    $this->cursor++;

                    break;
                } elseif ($keyPress === "\033[A") {
                    if ($prompt['type'] === 'select') {
                        $this->currentSelection = $this->currentSelection === 0 ? count($prompt['choices']) - 1 : $this->currentSelection - 1;
                    }
                } elseif ($keyPress === "\033[B") {
                    if ($prompt['type'] === 'select') {
                        $this->currentSelection = $this->currentSelection === count($prompt['choices']) - 1 ? 0 : $this->currentSelection + 1;
                    }
                } elseif ($keyPress === "\033[C") {
                    // right
                } elseif ($keyPress === "\033[D") {
                    // left
                } elseif ($keyPress === "\177") {
                    // backspace
                    $this->currentInput = substr($this->currentInput, 0, -1);
                    $this->renderPrompt($this->questions[$this->cursor]);
                    continue;
                }

                $this->currentInput .= $keyPress;
                $this->renderPrompt($this->questions[$this->cursor]);
            }
        }
    }

    protected function renderTextPrompt($prompt)
    {
        if ($prompt['answered'] ?? false) {
            sprout()->style()->write(
                "✔ {$prompt['message']}: › … {$this->answers[$prompt['cursor']]}" . PHP_EOL
            );
            return;
        }

        $output = $this->currentInput ?: $prompt['default'];

        sprout()->style()->write(
            "? {$prompt['message']}: › $output" . PHP_EOL
        );
    }

    protected function renderSelectPrompt($prompt)
    {
        if ($prompt['answered'] ?? false) {
            sprout()->style()->write(
                "✔ {$prompt['message']}: › … {$this->answers[$prompt['cursor']]}" . PHP_EOL
            );
            return;
        }

        sprout()->style()->writeln(
            "? {$prompt['message']}: › - Use arrow-keys. Return to submit."
        );

        foreach ($prompt['choices'] as $key => $option) {
            $selected = $this->currentSelection === $key ? '❯' : ' ';

            sprout()->style()->writeln(
                "$selected  {$option['title']}"
            );
        }
    }
}
