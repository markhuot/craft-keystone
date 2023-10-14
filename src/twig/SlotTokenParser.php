<?php

namespace markhuot\keystone\twig;

class SlotTokenParser extends \Twig\TokenParser\AbstractTokenParser
{
    public function parse(\Twig\Token $token)
    {
        $parser = $this->parser;
        $stream = $parser->getStream();

        if (! $stream->test(\Twig\Token::BLOCK_END_TYPE)) {
            $name = $this->parser->getExpressionParser()->parseExpression();
        } else {
            $name = null;
        }
        $stream->expect(\Twig\Token::BLOCK_END_TYPE);
        $defaultContent = $this->parser->subparse(fn (\Twig\Token $token) => $token->test('endslot'), true);
        $stream->expect(\Twig\Token::BLOCK_END_TYPE);

        return new SlotTokenNode($name, $defaultContent, $token->getLine(), $this->getTag());
    }

    public function getTag()
    {
        return 'slot';
    }
}
