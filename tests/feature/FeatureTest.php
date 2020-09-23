<?php

namespace tests\feature;

use izu\pite\TemplateCompiler;
use PHPUnit\Framework\TestCase;

class FeatureTest extends TestCase
{
    /**
     * @var TemplateCompiler
     */
    protected $compiler;

    function setUp () : void {
        $this->compiler = new TemplateCompiler();
        parent::setUp();
    }

    function testPlainText () {
        $template = $this->compiler->compile( "Plain text without templating features" );
        $this->assertEquals( "Plain text without templating features", $template->render( ['param' => 'value'] ) );
        $this->assertEquals( "Plain text without templating features", $template->render() );
    }

    function testForwardSlashes () {
        $template = $this->compiler->compile( "Forward slashes \`//\` to /( escape /) /{ anything /}" );
        $this->assertEquals( "Forward slashes `/` to ( escape ) { anything }", $template->render( ['anything' => 'value'] ) );
        $this->assertEquals( "Forward slashes `/` to ( escape ) { anything }", $template->render() );
    }

    function testSubstitutionParenthesis () {
        $template = $this->compiler->compile( "Parenthesis ( param ) substitution" );
        $this->assertEquals( "Parenthesis value substitution", $template->render( ['param' => 'value'] ) );
        $this->assertEquals( "Parenthesis substitution", $template->render() );
    }

    function testDefaultValueParenthesis () {
        $template = $this->compiler->compile( "Parenthesis ( param ) substitution" );
        $this->assertEquals( "Parenthesis value substitution", $template->render( ['param' => 'value'] ) );
        $this->assertEquals( "Parenthesis substitution", $template->render() );
    }

    function testOmissionBraceParam () {
        $template = $this->compiler->compile( "{ { param } propagate omission }" );
        $this->assertEquals( "value propagate omission", $template->render( ['param' => 'value'] ) );
        $this->assertEquals( "", $template->render() );
    }

    function testInvertedOmissionBraceParam () {
        $template = $this->compiler->compile( "{ { variable ? default value } - inverted omission } { and implicit empty non-null { variable ? } }" );
        $this->assertEquals( "", $template->render( ['param' => 'value'] ) );
        $this->assertEquals( "default value - inverted omission and implicit empty non-null", $template->render() );
    }

    function testParamPropertyAccess () {
        $template = $this->compiler->compile( "( param . property . subProperty ) - nested access" );
        $this->assertEquals( "value - nested access", $template->render( ['param' => ['property' => ['subProperty' => 'value']]] ) );
        $this->assertEquals( " - nested access", $template->render( ['param' => ['property' => ['subProperty' => null]]] ) );
    }

    function testMarginalScopeWhitespaceStripping () {
        $template = $this->compiler->compile( "       (      param  )  (param)   note(param)" );
        $this->assertEquals( "value value note value", $template->render( ['param' => 'value'] ) );
        $this->assertEquals( "note", $template->render() );
    }

    /**
     * note: Plaintext merges with scopes without whitespaces
     */
    function testPlainTextBackticks () {
        $template = $this->compiler->compile( "`  `( ` param ` )    ` `   ( ` param ` ) ` note ` (param)   ` .`" );
        $this->assertEquals( "  value value note  .", $template->render( [' param ' => 'value'] ) );
        $this->assertEquals( "note  .", $template->render() );
    }

    function testDefaultVariableOutputRules () {
        $template = $this->compiler->compile( "Output (param)" );
        $this->assertEquals( "Output 1", $template->render( ['param' => true] ) );
        $this->assertEquals( "Output", $template->render( ['param' => false] ) );
        $this->assertEquals( "Output", $template->render( ['param' => ""] ) );
        $this->assertEquals( "Output", $template->render( ['param' => null] ) );
    }

    function testScopeFunctionPassThrough () {
        $template = $this->compiler->compile(
            "First level: ( variable @ strtoupper )`; `( SECOND LEVEL: ( variable @ strtoupper ) @ strtolower )`; `{ In omission: { variable } @ strtoupper }"
        );
        $this->assertEquals( "First level - VALUE; second level: value; IN OMISSION: VALUE", $template->render( ['variable' => 'value'] ) );
    }

    /**
     * note: Single implicit whitespace unless specified
     */
    function testVariableSmartJoinTwiddle () {
        $template = $this->compiler->compile( "List join: (variable~,) {List join: {variable~`,`}}" );
        $this->assertEquals( "List join: a, b, c List join: a,b,c", $template->render( ['variable' => ['a', 'b', 'c']] ) );
        $this->assertEquals( "List join: value List join: value", $template->render( ['variable' => 'value'] ) );
        $this->assertEquals( "List join:", $template->render() );
    }

    function testJoinTwiddleOnlyMultiple () {
        $template = $this->compiler->compile( "List join: (variable?~,) {List join: {variable?~`,`}}" );
        $this->assertEquals( "List join: a, b, c List join: a,b,c", $template->render( ['variable' => ['a', 'b', 'c']] ) );
        $this->assertEquals( "List join:", $template->render( ['variable' => 'value'] ) );
        $this->assertEquals( "List join:", $template->render() );
    }

    function testOmissionIfEmptyOrMultiple () {
        $template = $this->compiler->compile( "{ List join: { param ?- } }" );
        $this->assertEquals( "", $template->render( ['param' => ['a', 'b', 'c']] ) );
        $this->assertEquals( "List join: value", $template->render( ['param' => 'value'] ) );
        $this->assertEquals( "", $template->render() );
    }

    function testListGapSkip () {
        $template = $this->compiler->compile( "{ List with gaps: { param ~, } }" );
        $this->assertEquals( "List with gaps: a, c", $template->render( ['param' => ['a', null, 'b']] ) );
    }

    function testCombinedOmissionWithLists () {
        $template = $this->compiler->compile( "
            Object has { following features: { object.features ?~; } }
            { a feature - { object.features ?- } }
            { object.features ? no features }
        " );
        $object = ['features' => []];
        $this->assertEquals( "Object has no features", $template->render( ['object' => $object] ) );
        $object = ['features' => 'feature'];
        $this->assertEquals( "Object has a feature - feature", $template->render( ['object' => $object] ) );
        $object = ['features' => ['a']];
        $this->assertEquals( "Object has a feature - a", $template->render( ['object' => $object] ) );
        $object = ['features' => ['a', 'b', 'c']];
        $this->assertEquals( "Object has following features: a; b; c", $template->render( ['object' => $object] ) );
    }

    function testCombinedOmission () {
        $template = $this->compiler->compile( "
            { movie ? Check back later for new titles! }
            { Hype for a new movie { movie.title } { movie.producer ? }! }
            { It's time for { movie.title } from producer of { movie.producer.produced ~ , } - { movie.producer.name } }
            { Starring { movie.starring ?~ , }! }
            { { movie.starring ?- } in the main cast! }
        " );
        $this->assertEquals( "", $template->render() );
    }

    function testEitherByOmission () {
        $template = $this->compiler->compile( "
            {There are only two options: {a} or {b}}
            {Your only option is {a}{b?}}
            {You have no other way but to {b}{a?}}
            {There is no hope for you...{a?}{b?}}
        " );
        $this->assertEquals( "", $template->render() );
    }
}
