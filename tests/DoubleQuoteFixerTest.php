<?php
/**
 * MIT License
 *
 * Copyright (c) 2022 Androl Genhald (tests & example strings)
 * Copyright (c) 2022 Noah Boegli (merging & management)
 * Copyright (c) Fabien Potencier, Dariusz Rumiński (assertTokens function)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

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
