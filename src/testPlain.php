<?php

require __DIR__ . "/../vendor/autoload.php";


function m_root ( $input, $pointer, $branch, &$tokens ) {
    $end = strlen( $input );
    $buffer = "";
    while ( $pointer < $end ) {
        $switch = [
                $tokens[0] => 'm_block',
            ][ $input[ $pointer ] ] ?? null;
        if ( isset( $switch ) ) {
            $branch []= $buffer;
            $buffer = "";
            [ $pointer, $subBranch ] = $switch( $input, $pointer + 1, [], $tokens );
            $branch []= $subBranch;
        } else {
            $buffer .= $input[ $pointer ];
        }
        $pointer ++;
    }
    $branch []= $buffer;
    return [ $pointer, $branch ];
};

function m_block ( $input, $pointer, $branch, &$tokens ) {
    $end = strlen( $input );
    $buffer = "";
    $startAt = $pointer;
    while ( $pointer < $end ) {
        $switch = [
                $tokens[0] => 'm_block',
            ][ $input[ $pointer ] ] ?? null;
        if ( isset( $switch ) ) {
            $branch []= $buffer;
            $buffer = "";
            [ $pointer, $subBranch ] = $switch( $input, $pointer + 1, [], $tokens );
            $branch []= $subBranch;
        } else {
            if ( $tokens[1] == $input[ $pointer ] ) {
                $branch []= $buffer;
                return [ $pointer, $branch ];
            } else {
                $buffer .= $input[ $pointer ];
            }
        }
        $pointer ++;
    }
    throw new Exception( "Unclosed block starts at $startAt" );
};

$input = " aaa ( bbb ( ccc ) ddd ) eee ";

$tokens = [
    0 => '(',
    1 => ')',
    2 => '{',
    3 => '}',
];

$parsed = m_root( $input, 0, [], $tokens );

var_dump( $parsed );
