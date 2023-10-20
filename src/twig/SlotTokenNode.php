<?php

namespace markhuot\keystone\twig;

class SlotTokenNode extends \Twig\Node\Node
{
    public function __construct($attributes, \Twig\Node\Node $defaultContent, $line, $tag = null)
    {
        parent::__construct(['defaultContent' => $defaultContent], $attributes, $line, $tag);
    }

    public function compile(\Twig\Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write('echo $context[\'component\']?->defineSlot(');

        $name = $this->getAttribute('name');
        if ($name) {
            $compiler->subcompile($this->getAttribute('name'));
        } else {
            $compiler->write('null');
        }

        $compiler->write(')');

        $allow = $this->getAttribute('allow');
        if ($allow) {
            $compiler->write('->allow(');
            $compiler->subcompile($this->getAttribute('allow'));
            $compiler->write(')');
        }

        $compiler->write(';'.PHP_EOL);
    }
}
