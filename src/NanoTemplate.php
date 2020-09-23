<?php

class NanoRegexTemplate
{
    public $cutFlag = '@@@';

    function templatePlaceholders ( string $template ) {
        $matches = [];
        preg_match_all( "~\{\s*[^{}]+\s*\}~u", $template, $matches );
        return array_unique( $matches[ 0 ] );
    }

    function replacementMap ( string $template, array $data ) {
        $placeholders = $this->templatePlaceholders( $template );
        $replacements = [];
        foreach ( $placeholders as $placeholder ) {
            $key = trim( $placeholder, "{} " );
            $replacements[ $placeholder ] = $data[ $key ] ?? $this->cutFlag;
        }
        return $replacements;
    }

    function strip ( $string ) {
        return trim( preg_replace( '/[\s]([\.\,\:\;\?\!])/u', '$1',
            preg_replace( '/[\s]+([^\s])/u', ' $1', $string )) );
    }

    function renderTemplate ( string $template, array $data = [] ) {
        $replacements = $this->replacementMap( $template, $data );
        $compiled = str_replace( array_keys( $replacements ), array_values( $replacements ), $template );
        while ( $compiled !== $cropped = preg_replace( [ "/\{[^{}]*{$this->cutFlag}[^{}]*\}/u", "/\{([^{({$this->cutFlag})]*)\}/u" ], [ $this->cutFlag, '$1' ], $compiled ) ) {
            $compiled = $cropped;
        }
        $compiled = preg_replace( "/{$this->cutFlag}/u", "", $compiled );
        return $this->strip( $compiled );
    }
}