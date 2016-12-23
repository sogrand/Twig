<?php

/*
 * This file is part of Twig.
 *
 * (c) 2009 Fabien Potencier
 * (c) 2009 Armin Ronacher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Represents a token stream.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
final class Twig_TokenStream
{
    private $tokens;
    private $current = 0;
    private $source;

    public function __construct(array $tokens, Twig_Source $source = null)
    {
        $this->tokens = $tokens;
        $this->source = $source ?: new Twig_Source('', '');
    }

    public function __toString()
    {
        return implode("\n", $this->tokens);
    }

    public function injectTokens(array $tokens)
    {
        $this->tokens = array_merge(array_slice($this->tokens, 0, $this->current), $tokens, array_slice($this->tokens, $this->current));
    }

    /**
     * Sets the pointer to the next token and returns the old one.
     */
    public function next(): Twig_Token
    {
        if (!isset($this->tokens[++$this->current])) {
            throw new Twig_Error_Syntax('Unexpected end of template.', $this->tokens[$this->current - 1]->getLine(), $this->source);
        }

        return $this->tokens[$this->current - 1];
    }

    /**
     * Tests a token, sets the pointer to the next one and returns it or throws a syntax error.
     *
     * @return Twig_Token|null The next token if the condition is true, null otherwise
     */
    public function nextIf($primary, $secondary = null)
    {
        if ($this->tokens[$this->current]->test($primary, $secondary)) {
            return $this->next();
        }
    }

    /**
     * Tests a token and returns it or throws a syntax error.
     */
    public function expect($type, $value = null, $message = null): Twig_Token
    {
        $token = $this->tokens[$this->current];
        if (!$token->test($type, $value)) {
            $line = $token->getLine();
            throw new Twig_Error_Syntax(sprintf('%sUnexpected token "%s" of value "%s" ("%s" expected%s).',
                $message ? $message.'. ' : '',
                Twig_Token::typeToEnglish($token->getType()), $token->getValue(),
                Twig_Token::typeToEnglish($type), $value ? sprintf(' with value "%s"', $value) : ''),
                $line,
                $this->source
            );
        }
        $this->next();

        return $token;
    }

    /**
     * Looks at the next token.
     */
    public function look(int $number = 1): Twig_Token
    {
        if (!isset($this->tokens[$this->current + $number])) {
            throw new Twig_Error_Syntax('Unexpected end of template.', $this->tokens[$this->current + $number - 1]->getLine(), $this->source);
        }

        return $this->tokens[$this->current + $number];
    }

    /**
     * Tests the current token.
     */
    public function test($primary, $secondary = null): bool
    {
        return $this->tokens[$this->current]->test($primary, $secondary);
    }

    public function isEOF(): bool
    {
        return $this->tokens[$this->current]->getType() === Twig_Token::EOF_TYPE;
    }

    public function getCurrent(): Twig_Token
    {
        return $this->tokens[$this->current];
    }

    /**
     * @internal
     */
    public function getSourceContext(): Twig_Source
    {
        return $this->source;
    }
}
