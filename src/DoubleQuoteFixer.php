<?php
/**
 * MIT License
 *
 * Copyright (c) 2021 Noah Boegli (adaptation to new fixer interface, comments & improvements)
 * Copyright (c) 2015 Fabien Potencier (for the base code used inside the loop, see details lines 54-68)
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

namespace GD75\DoubleQuoteFixer;

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

/**
 * DoubleQuoteFixer, replaces single quotes with double quotes.
 */
final class DoubleQuoteFixer implements FixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_CONSTANT_ENCAPSED_STRING);
    }

    /**
     * {@inheritdoc}
     */
    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        /*
         * This code was taken from an already existing double quote fixer and adapted to work with the latest version,
         * some comments were also added to clarify why the checks were being done.
         * https://github.com/mpociot/PHP-CS-Fixer/blob/master/Symfony/CS/Fixer/Contrib/DoubleQuoteFixer.php
         *
         * Original license:
         * -----------------------------------------------------------------------------------
         *     This file is part of the PHP CS utility.
         *
         *     (c) Fabien Potencier <fabien@symfony.com>
         *
         *     This source file is subject to the MIT license that is bundled
         *     with this source code in the file LICENSE.
         *     Said LICENSE file can be found here: https://github.com/mpociot/PHP-CS-Fixer/blob/master/LICENSE
         * -----------------------------------------------------------------------------------
         */
        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind(T_CONSTANT_ENCAPSED_STRING)) {
                continue;
            }

            $content = $token->getContent();

            /*
             * Checking that the string
             * 1. Starts with '
             * 2. Does not contain any ", if it does, the string might be voluntarily using single quotes to avoid
             *    escaping hell. This would be interesting to be toggleable with an option...
             * 3. Does not contain any sequence that would be interpreted as an escape sequence by a double-quoted
             *    string (cf: https://www.php.net/manual/en/language.types.string.php#language.types.string)
             */
            if (
                "'" === $content[0] &&
                false === strpos($content, '"') &&
                // regex: odd number of backslashes, not followed by double quote or dollar
                !preg_match("/(?<!\\\\)(?:\\\\{2})*\\\\(?!['$\\\\])/", $content)
            ) {
                // Stripping extremities of the string (removing the two ')
                $content = substr($content, 1, -1);

                // Removed escaped '
                $content = str_replace("\\'", "'", $content);
				// Escape $
                $content = str_replace("$", "\\$", $content);
				// Escape \
                $content = str_replace("\\", "\\\\", $content);

                // Replacing the content in the tokens
                $tokens->clearAt($index);
                $tokens->insertAt($index, new Token("\"$content\""));
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function isRisky(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getDefinition(): FixerDefinitionInterface
    {
        return new class() implements FixerDefinitionInterface {
            public function getSummary(): string
            {
                return "Enforces double quotes over single quotes.";
            }

            public function getDescription(): ?string
            {
                return null;
            }

            /**
             * @inheritDoc
             */
            public function getRiskyDescription(): ?string
            {
                return null;
            }

            /**
             * @inheritDoc
             */
            public function getCodeSamples(): array
            {
                return [];
            }
        };
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return "GD75/double_quote_fixer";
    }

    /**
     * @inheritDoc
     */
    public function getPriority(): int
    {
        return 1;
    }

    /**
     * @inheritDoc
     */
    public function supports(SplFileInfo $file): bool
    {
        return $file->getExtension() === "php";
    }
}
