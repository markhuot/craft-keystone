<?php

namespace markhuot\keystone\twig;

class SlotTokenNode extends \Twig\Node\Node
{
    public function __construct($name, \Twig\Node\Node $defaultContent, $line, $tag = null)
    {
        parent::__construct(['defaultContent' => $defaultContent], ['name' => $name], $line, $tag);
    }

    public function compile(\Twig\Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write('echo $context[\'component\']?->getSlot(');

        $name = $this->getAttribute('name');
        if ($name) {
            $compiler->subcompile($this->getAttribute('name'));
        } else {
            $compiler->write('null');
        }

        $compiler->write(')->toHtml();'.PHP_EOL);
    }
}
