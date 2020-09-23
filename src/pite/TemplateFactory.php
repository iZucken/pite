<?php

namespace izu\pite;

use Nette\Tokenizer\Tokenizer;

class TemplateFactory
{
    /**
     * @var Tokenizer
     */
    private $tokenizer;

    function __construct ( ?Tokenizer $tokenizer = null ) {
        if ( empty( $tokenizer ) ) {
            $tokenizer = new Tokenizer( (new DefaultDictionary())->values() );
        }
        $this->tokenizer = $tokenizer;
    }

    function fromString ( $template ) {
        $tokenized = $this->tokenizer->tokenize( $template );
        var_dump( $tokenized->tokens );



        $template = new Template( $template, $tokenized );
        return $template;
    }
}