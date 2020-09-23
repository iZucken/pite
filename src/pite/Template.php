<?php

namespace izu\pite;

class Template
{
    /**
     * @var string
     */
    private $source;

    function __construct ( string $source) {
        $this->source = $source;
    }

    function render ( $values = [] ) {
        return $this->source;
    }
}