<?php

namespace markhuot\keystone\twig;

class ExportTokenParser extends \Twig\TokenParser\AbstractTokenParser
{
    public function parse(\Twig\Token $token)
    {
        $parser = $this->parser;
        $stream = $parser->getStream();

        $name = $stream->expect(\Twig\Token::NAME_TYPE)->getValue();

        if ($stream->nextIf(\Twig\Token::OPERATOR_TYPE, '=')) {
            $capture = false;
            $value = $this->parser->getExpressionParser()->parseExpression();
            $stream->expect(\Twig\Token::BLOCK_END_TYPE);
        } else {
            $capture = true;
            $stream->expect(\Twig\Token::BLOCK_END_TYPE);
            $value = $this->parser->subparse(fn (\Twig\Token $token) => $token->test('endexport'), true);
            $stream->expect(\Twig\Token::BLOCK_END_TYPE);
        }

        return new ExportTokenNode($capture, $name, $value, $token->getLine(), $this->getTag());
    }

    public function getTag()
    {
        return 'export';
    }
}
