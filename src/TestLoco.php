<?php

use Ferno\Loco\ConcParser;
use Ferno\Loco\EmptyParser;
use Ferno\Loco\Grammar;
use Ferno\Loco\GreedyMultiParser;
use Ferno\Loco\GreedyStarParser;
use Ferno\Loco\LazyAltParser;
use Ferno\Loco\RegexParser;
use Ferno\Loco\StringParser;

require __DIR__ . "/../vendor/autoload.php";

$grammarCallback = function ( $syntax ) {
    $parsers = array ();
    foreach ( $syntax as $rule ) {
        if ( count( $parsers ) === 0 ) {
            $top = $rule[ "rule-name" ];
        }
        $parsers[ $rule[ "rule-name" ] ] = $rule[ "expression" ];
    }
    if ( count( $parsers ) === 0 ) {
        throw new Exception( "No rules." );
    }
    return new Grammar( $top, $parsers );
};

$picoteGrammar = new Grammar(
    "<syntax>",
    [
        "<syntax>" => new ConcParser(
            array ( "<space>", "<rules>" ),
            function ( $space, $rules ) {
                return $rules;
            }
        ),

        "<rules>" => new GreedyStarParser( "<rule>" ),

        "<rule>" => new ConcParser(
            array ( "<bareword>", "<space>", new StringParser( "=" ), "<space>", "<alt>", new StringParser( ";" ), "<space>" ),
            function ( $bareword, $space1, $equals, $space2, $alt, $semicolon, $space3 ) {
                return array (
                    "rule-name" => $bareword,
                    "expression" => $alt
                );
            }
        ),

        "<alt>" => new ConcParser(
            array ( "<conc>", "<pipeconclist>" ),
            function ( $conc, $pipeconclist ) {
                array_unshift( $pipeconclist, $conc );
                return new LazyAltParser( $pipeconclist );
            }
        ),

        "<pipeconclist>" => new GreedyStarParser( "<pipeconc>" ),

        "<pipeconc>" => new ConcParser(
            array ( new StringParser( "|" ), "<space>", "<conc>" ),
            function ( $pipe, $space, $conc ) {
                return $conc;
            }
        ),

        "<conc>" => new ConcParser(
            array ( "<term>", "<commatermlist>" ),
            function ( $term, $commatermlist ) {
                array_unshift( $commatermlist, $term );

                // get array key numbers where multiparsers are located
                // in reverse order so that our splicing doesn't modify the array
                $multiparsers = array ();
                foreach ( $commatermlist as $k => $internal ) {
                    if ( is_a( $internal, "GreedyMultiParser" ) ) {
                        array_unshift( $multiparsers, $k );
                    }
                }

                // We do something quite advanced here. The inner multiparsers are
                // spliced out into the list of arguments proper instead of forming an
                // internal sub-array of their own
                return new ConcParser(
                    $commatermlist,
                    function () use ( $multiparsers ) {
                        $args = func_get_args();
                        foreach ( $multiparsers as $k ) {
                            array_splice( $args, $k, 1, $args[ $k ] );
                        }
                        return $args;
                    }
                );
            }
        ),

        "<commatermlist>" => new GreedyStarParser( "<commaterm>" ),

        "<commaterm>" => new ConcParser(
            array ( new StringParser( "," ), "<space>", "<term>" ),
            function ( $comma, $space, $term ) {
                return $term;
            }
        ),

        "<term>" => new LazyAltParser(
            array ( "<bareword>", "<sq>", "<dq>", "<group>", "<repetition>", "<optional>" )
        ),

        "<bareword>" => new ConcParser(
            array (
                new RegexParser(
                    "#^([a-z][a-z ]*[a-z]|[a-z])#",
                    function ( $match0 ) {
                        return $match0;
                    }
                ),
                "<space>"
            ),
            function ( $bareword, $space ) {
                return $bareword;
            }
        ),

        "<group>" => new ConcParser(
            array (
                new StringParser( "(" ),
                "<space>",
                "<alt>",
                new StringParser( ")" ),
                "<space>"
            ),
            function ( $left_paren, $space1, $alt, $right_paren, $space2 ) {
                return $alt;
            }
        ),

        "<repetition>" => new ConcParser(
            array (
                new StringParser( "{" ),
                "<space>",
                "<alt>",
                new StringParser( "}" ),
                "<space>"
            ),
            function ( $left_brace, $space1, $alt, $right_brace, $space2 ) {
                return new GreedyStarParser( $alt );
            }
        ),

        "<optional>" => new ConcParser(
            [
                new StringParser( "[" ),
                "<space>",
                "<alt>",
                new StringParser( "]" ),
                "<space>"
            ],
            function ( $left_bracket, $space1, $alt, $right_bracket, $space2 ) {
                return new GreedyMultiParser( $alt, 0, 1 );
            }
        ),

        "<preserved>" => new RegexParser( "/'[^']+/" ),
        "<word>" => new RegexParser( "/[\w\d]+/" ),
        "<whitespace>" => new RegexParser( "/\s+/" ),
    ],
    $grammarCallback
);

$template = <<<PICOTE
some template { braces content { omit variable } }
PICOTE;

$parsed = $picoteGrammar->parse( $template );

var_dump( $parsed );

