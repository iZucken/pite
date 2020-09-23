<?php

namespace izu\pite;

class DefaultDictionary
{
    public function values() {
        return [
            Tokens::ESCAPE => "\/",
            Tokens::BLOCK_OPEN => "\(",
            Tokens::BLOCK_CLOSE => "\)",
            Tokens::OMIT_OPEN => "\{",
            Tokens::OMIT_CLOSE => "\}",
            Tokens::DEFAULT_TO => "\?",
            Tokens::DEFAULT_SINGLE => "\?\-",
            Tokens::GLUE => "\~",
            Tokens::GLUE_OMIT => "\?\~",
            Tokens::PRESERVE => "\'",
            Tokens::WHITESPACE => "\s+",
            Tokens::ANY => ".+\b",
        ];
    }
}