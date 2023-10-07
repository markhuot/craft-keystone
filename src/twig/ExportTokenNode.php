<?php

namespace markhuot\keystone\twig;

class ExportTokenNode extends \Twig\Node\Node
{
    public function __construct(bool $capture, $name, \Twig\Node\Node $value, $line, $tag = null)
    {
        parent::__construct(['value' => $value], ['name' => $name, 'capture' => $capture], $line, $tag);
    }

    public function compile(\Twig\Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write($this->getAttribute('capture') ? 'ob_start();'.PHP_EOL : '$value=')
            ->subcompile($this->getNode('value'))
            ->write($this->getAttribute('capture') ? '$value=ob_get_contents();ob_end_clean();'.PHP_EOL : '')
            ->write(';'.PHP_EOL)
            ->write('($context[\'exports\'] ?? new class{function add(){}})->add(\'' . $this->getAttribute('name') . '\', $value);'.PHP_EOL)
        ;
    }
}
