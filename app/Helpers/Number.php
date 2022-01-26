<?php

namespace App\Helpers;

class Number
{
    protected string $original_value;

    public function __construct($value)
    {
        $this->original_value = (string) $value;
    }

    public function toInteger(): int
    {
        return intval($this->original_value);
    }

    public function toFloat(): float
    {
        return (float) str_replace(
            ',',
            '.',
            str_replace(
                '.',
                '',
                $this->original_value
            )
        );
    }
}
