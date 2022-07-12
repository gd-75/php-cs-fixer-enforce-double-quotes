<?php

declare(strict_types=1);

namespace GD75\DoubleQuoteFixer\Tests;

use GD75\DoubleQuoteFixer\DoubleQuoteFixer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

class DoubleQuoteFixerTest extends TestCase
{
    private DoubleQuoteFixer $fixer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixer = new DoubleQuoteFixer();
    }

    /**
     * @dataProvider providerFixDoubleQuote
     * @covers \GD75\DoubleQuoteFixer\DoubleQuoteFixer
     */
    public function testFixDoubleQuote(string $input, string $expectedOutput): void
    {
        Tokens::clearCache();
        $tokens = Tokens::fromCode($input);

        $dummyFile = new SplFileInfo(tempnam(sys_get_temp_dir(), "double_quote_fixer_dummy"));

        $this->fixer->fix($dummyFile, $tokens);

        $tokens->clearEmptyTokens();

        Tokens::clearCache();
        $expectedTokens = Tokens::fromCode($expectedOutput);
        static::assertTokens($expectedTokens, $tokens);
    }

    /**
     * Copied from PhpCsFixer\Tokenizer\Tokens\AssertTokensTrait (c) Fabien Potencier <fabien@symfony.com>, Dariusz Rumiński <dariusz.ruminski@gmail.com>.
     */
    private static function assertTokens(Tokens $expectedTokens, Tokens $inputTokens): void
    {
        foreach ($expectedTokens as $index => $expectedToken) {
            if (!isset($inputTokens[$index])) {
                static::fail(sprintf("The token at index %d must be:\n%s, but is not set in the input collection.", $index, $expectedToken->toJson()));
            }

            $inputToken = $inputTokens[$index];

            static::assertTrue(
                $expectedToken->equals($inputToken),
                sprintf("The token at index %d must be:\n%s,\ngot:\n%s.", $index, $expectedToken->toJson(), $inputToken->toJson())
            );

            $expectedTokenKind = $expectedToken->isArray() ? $expectedToken->getId() : $expectedToken->getContent();
            static::assertTrue(
                $inputTokens->isTokenKindFound($expectedTokenKind),
                sprintf(
                    "The token kind %s (%s) must be found in tokens collection.",
                    $expectedTokenKind,
                    \is_string($expectedTokenKind) ? $expectedTokenKind : Token::getNameForId($expectedTokenKind)
                )
            );
        }

        static::assertSame($expectedTokens->count(), $inputTokens->count(), "Both collections must have the same length.");
    }

    /**
     * @return iterable<string, array{input: string, output: string}>
     */
    public function providerFixDoubleQuote(): iterable
    {
        yield "variableEscaped" => [
            "input" => <<<'PHP'
                <?php
                echo '$foobar';
            PHP,
            "output" => <<<'PHP'
                <?php
                echo "\$foobar";
            PHP,
        ];
        yield "backslashEscaped" => [
            "input" => <<<'PHP'
                <?php
                echo '\foobar';
            PHP,
            "output" => <<<'PHP'
                <?php
                echo "\\foobar";
            PHP,
        ];
        yield "backslashAndVariableEscaped" => [
            "input" => <<<'PHP'
                <?php
                echo '\$foobar';
            PHP,
            "output" => <<<'PHP'
                <?php
                echo "\\\$foobar";
            PHP,
        ];
        yield "alreadyEscapedBackslash" => [
            "input" => <<<'PHP'
                <?php
                echo '\\';
            PHP,
            "output" => <<<'PHP'
                <?php
                echo "\\";
            PHP,
        ];
        yield "alreadyEscapedBackslashWithVariable" => [
            "input" => <<<'PHP'
                <?php
                echo '\\$foobar';
            PHP,
            "output" => <<<'PHP'
                <?php
                echo "\\\$foobar";
            PHP,
        ];
        yield "unescapeSingleQuote" => [
            "input" => <<<'PHP'
                <?php
                echo '\'';
            PHP,
            "output" => <<<'PHP'
                <?php
                echo "'";
            PHP,
        ];
        yield "unescapeSingleQuoteAfterBackslash" => [
            "input" => <<<'PHP'
                <?php
                echo '\\\'';
            PHP,
            "output" => <<<'PHP'
                <?php
                echo "\\'";
            PHP,
        ];
        yield "threeBackslashes" => [
            "input" => <<<'PHP'
                <?php
                echo '\\\foobar';
            PHP,
            "output" => <<<'PHP'
                <?php
                echo "\\\\foobar";
            PHP,
        ];
        yield "fiveBackslashes" => [
            "input" => <<<'PHP'
                <?php
                echo '\\\\\foobar';
            PHP,
            "output" => <<<'PHP'
                <?php
                echo "\\\\\\foobar";
            PHP,
        ];
        yield "fiveBackslashesTwice" => [
            "input" => <<<'PHP'
                <?php
                echo '\\\\\foo\\\\\bar';
            PHP,
            "output" => <<<'PHP'
                <?php
                echo "\\\\\\foo\\\\\\bar";
            PHP,
        ];
        yield "testEscapedNewline" => [
            "input" => <<<'PHP'
                <?php
                echo '\n';
            PHP,
            "output" => <<<'PHP'
                <?php
                echo "\\n";
            PHP,
        ];
    }
}
