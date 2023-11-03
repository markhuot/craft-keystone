<?php

namespace markhuot\keystone\actions;

use Craft;
use markhuot\keystone\base\AttributeBag;
use markhuot\keystone\base\ComponentType;
use markhuot\keystone\base\FieldDefinition;
use markhuot\keystone\base\SlotDefinition;
use markhuot\keystone\models\Component;
use markhuot\keystone\models\ComponentData;
use markhuot\keystone\twig\Exports;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;
use RuntimeException;

class CompileTwigComponent
{
    public function __construct(
        protected $twigPath,
        protected $handle,
    ) {
    }

    public function handle($force = false)
    {
        [$viewMode, $twigPath] = explode(':', $this->twigPath);

        if (! ($filesystemPath = Craft::$app->getView()->resolveTemplate($twigPath, $viewMode))) {
            throw new RuntimeException('Could not find template at '.$twigPath);
        }

        $filemtime = (new GetFileMTime)->handle($filesystemPath);
        $compiledClassesPath = rtrim(Craft::$app->getPath()->getCompiledClassesPath(), '/').'/';
        $hash = sha1($this->handle);
        $className = 'ComponentType'.$hash.$filemtime;
        $fqcn = '\\keystone\\cache\\'.$className;

        // Bail early if the cache already exists
        if (! $force && file_exists($compiledClassesPath.$className.'.php')) {
            require_once $compiledClassesPath.$className.'.php';

            return $fqcn;
        }

        $fullTwigPath = Craft::$app->getView()->resolveTemplate($twigPath, $viewMode);

//        $component = new Component;
//        $props = new ComponentData;
//        $props->type = $this->handle;
//        $component->populateRelation('data', $props);
//        Craft::$app->getView()->renderTemplate($twigPath, [
//            'component' => $component,
//            'exports' => $exports = new Exports,
//            'props' => new ComponentData,
//            'attributes' => new AttributeBag,
//        ], $viewMode);

//        $slotNames = $component->getAccessed()->map(fn (SlotDefinition $defn) => $defn->getConfig())->toArray();

//        $slotNameArray = '<'.'?php '.var_export($slotNames, true).';';

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
//        $slotNameAst = $parser->parse($slotNameArray)[0]->expr;

        $ast = $parser->parse(file_get_contents(__DIR__.'/../base/ComponentType.php'));

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new class($this->handle, $viewMode.':'.$twigPath, [], $className) extends NodeVisitorAbstract
        {
            public function __construct(
                protected string $handle,
                protected string $twigPath,
                protected array $exports,
                protected string $className,
            ) {
            }

            public function enterNode(Node $node)
            {
                if ($node instanceof Namespace_) {
                    $node->name->parts = ['keystone', 'cache'];
                }
                if ($node instanceof Stmt\Class_) {
                    $node->flags = $node->flags & (~Stmt\Class_::MODIFIER_ABSTRACT);
                    $node->name->name = $this->className;
                    $node->extends = new Node\Name\FullyQualified(ComponentType::class);
                }
                if ($node instanceof Stmt\Property && $node->props[0]->name->name === 'handle') {
                    $node->props[0]->default = new Node\Scalar\String_($this->exports['type'] ?? $this->handle);
                }
                if ($node instanceof Stmt\Property && $node->props[0]->name->name === 'category' && ! empty($this->exports['category'])) {
                    $node->props[0]->default = new Node\Scalar\String_($this->exports['category']);
                }
                if ($node instanceof Stmt\Property && $node->props[0]->name->name === 'name' && ! empty($this->exports['name'])) {
                    $node->props[0]->default = new Node\Scalar\String_($this->exports['name']);
                }
                if ($node instanceof Stmt\Property && $node->props[0]->name->name === 'icon') {
                    if ($this->exports['icon'] ?? false) {
                        $node->props[0]->default = new Node\Scalar\String_($this->exports['icon']);
                    }
                }
                if ($node instanceof Stmt\ClassMethod && $node->name->name === 'getTemplatePath') {
                    $node->stmts = [
                        new Stmt\Return_(new Node\Scalar\String_($this->twigPath)),
                    ];
                }
                if ($node instanceof Stmt\ClassMethod && $node->name->name === 'getFieldConfig') {
                    $node->stmts = [
                        new Stmt\Return_(new Node\Expr\Array_([])),
                    ];
                }
                if ($node instanceof Stmt\ClassMethod && $node->name->name === 'getSlotConfig') {
                    $node->stmts = [
                        new Stmt\Return_(new Node\Expr\Array_([])),
                    ];
                }
            }

            public function leaveNode(Node $node)
            {
                // If we're not setting an icon, remove the subclassed override so we can inherit the parent/generic icon
                if (empty($this->exports['icon']) && $node instanceof Stmt\Property && $node->props[0]->name->name === 'icon') {
                    return NodeTraverser::REMOVE_NODE;
                }

                // remove all non-abstract methods from our compiled classes so we can lean on inheritance/parent
                if ($node instanceof Stmt\ClassMethod && ! ($node->flags & Stmt\Class_::MODIFIER_ABSTRACT)) {
                    return NodeTraverser::REMOVE_NODE;
                }

                // now that all non-abstract methods are remove, we can remove the abstract flag so we get concrete
                // implementations of the abstract methods in our cached classes
                elseif ($node instanceof Stmt\ClassMethod) {
                    $node->flags = $node->flags & (~Stmt\Class_::MODIFIER_ABSTRACT);
                }
            }
        });
        $ast = $traverser->traverse($ast);

        $prettyPrinter = new PrettyPrinter\Standard;
        file_put_contents($compiledClassesPath.$className.'.php', $prettyPrinter->prettyPrintFile($ast));
        require_once $compiledClassesPath.$className.'.php';

        // Delete old versions of this cache
        $files = glob($compiledClassesPath.'ComponentType'.$hash.'*');
        foreach ($files as $file) {
            if ($file !== $compiledClassesPath.$className.'.php') {
                unlink($file);
            }
        }

        return $fqcn;
    }
}
