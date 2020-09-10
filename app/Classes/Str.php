<?php

namespace App\Classes;

class Str
{
    private $string;

    public function __construct(string $string)
    {
        $this->string = $string;
    }



    public function br2nl()
    {
        $this->string = preg_replace('/<br(\s+)?\/?>/i', "\n", $this->string);
        return $this;
    }

    public function explode(string $delimiter)
    {
        return explode($delimiter, $this->string);
    }

    public function __toString()
    {
        return $this->string;
    }
}
