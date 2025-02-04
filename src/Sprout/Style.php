<?php

declare(strict_types=1);

namespace Leaf\Sprout;

class Style
{
    public function apply(): Style
    {
        return $this;
    }

    public function to(): Style
    {
        return $this;
    }

    public function build(): string
    {
        return "";
    }

    /**
     * Write output to console
     * @param string $data
     * @return void
     */
    public function write(string $data)
    {
        echo $data;
    }

    /**
     * Write output to console with a new line
     * @param string $data
     * @return void
     */
    public function writeln(string $data)
    {
        $this->write($data . PHP_EOL);
    }

    public function __tostring(): string
    {
        return $this->build();
    }
}
