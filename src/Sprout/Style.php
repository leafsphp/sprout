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

    public function __tostring(): string
    {
        return $this->build();
    }
}
