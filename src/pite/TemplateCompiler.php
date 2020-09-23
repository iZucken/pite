<?php

namespace izu\pite;

class TemplateCompiler
{
    function compile (string $source) : Template {
        return new Template($source);
    }
}