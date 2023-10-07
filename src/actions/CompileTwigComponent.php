<?php

namespace markhuot\keystone\actions;

use Craft;
use craft\helpers\App;
use craft\web\View;
use markhuot\keystone\base\ComponentData;
use markhuot\keystone\base\ComponentType;
use markhuot\keystone\base\FieldDefinition;
use markhuot\keystone\models\Component;
use PhpParser\Builder\Class_;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use RuntimeException;

class CompileTwigComponent
{
    public function __construct(
        protected $twigPath,
        protected $handle,
    ) { }

    public function handle()
    {
        $viewMode = str_starts_with($this->twigPath, 'cp:') ? View::TEMPLATE_MODE_CP : View::TEMPLATE_MODE_SITE;
        $twigPath = preg_replace('/^cp:/', '', $this->twigPath);

        if (! ($filesystemPath = Craft::$app->getView()->resolveTemplate($twigPath, $viewMode))) {
            throw new RuntimeException('Could not find template at ' . $twigPath);
        }

        $filemtime = (new GetFileMTime)->handle($filesystemPath);
        $compiledClassesPath = App::parseEnv('@storage/runtime/compiled_classes/');
        $hash = sha1($this->handle);
        $className = 'ComponentType'.$hash.$filemtime;
        $fqcn = '\\keystone\\cache\\'.$className;

        // Bail early if the cache already exists
        if (file_exists($compiledClassesPath.$className.'.php')) {
            require_once($compiledClassesPath.$className.'.php');
            return $fqcn;
        }


        $props = new ComponentData;
        $exports = new class { public $exports = []; public function add($key, $value) { $this->exports[$key] = $value; } };
        $fullTwigPath = Craft::$app->getView()->resolveTemplate($twigPath, $viewMode);
        preg_match_all('/\{% slot\s*(\w+)?/', file_get_contents($fullTwigPath), $slots);
        $slotNames = collect($slots[1])->map(fn ($slot) => $slot === '' ? null : $slot)->toArray();
        Craft::$app->getView()->renderTemplate($twigPath, [
            'component' => new Component,
            'exports' => $exports,
            'props' => $props,
        ], $viewMode);
        $propTypes = $props->getAccessed()
            ->each(fn (FieldDefinition $defn, string $key) => $defn->config = array_merge($defn->config, $exports->exports['propTypes'][$key]->config ?? []))
            ->each(fn (FieldDefinition $defn, string $key) => $defn->handle($key))
            ->map(fn (FieldDefinition $defn) => $defn->config)
            ->toArray();

        $slotNameArray = '<'.'?php '. var_export($slotNames, true) . ';';
        $propTypeArray = '<'.'?php '. var_export($propTypes, true) . ';';

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $propTypeAst = $parser->parse($propTypeArray)[0]->expr;
        $slotNameAst = $parser->parse($slotNameArray)[0]->expr;

        $ast = $parser->parse(file_get_contents(__DIR__ . '/../base/ComponentType.php'));

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new class ($this->handle, $twigPath, $exports->exports, $propTypeAst, $className, $slotNameAst) extends NodeVisitorAbstract {
            public function __construct(
                protected string $handle,
                protected string $twigPath,
                protected array $exports,
                protected $propTypes,
                protected string $className,
                protected $slotNames,
            ) { }
            public function enterNode(Node $node) {
                if ($node instanceof Namespace_) {
                    $node->name->parts = ['keystone','cache'];
                }
                if ($node instanceof Stmt\Class_) {
                    $node->flags = $node->flags & (~ Stmt\Class_::MODIFIER_ABSTRACT);
                    $node->name->name = $this->className;
                    $node->extends = new Node\Name\FullyQualified(ComponentType::class);
                }
                if ($node instanceof Stmt\Property && $node->props[0]->name->name === 'handle') {
                    $node->props[0]->default = new Node\Scalar\String_($this->exports['type'] ?? $this->handle);
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
                        new Stmt\Return_($this->propTypes),
                    ];
                }
                if ($node instanceof Stmt\ClassMethod && $node->name->name === 'getSlotConfig') {
                    $node->stmts = [
                        new Stmt\Return_($this->slotNames),
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
                if ($node instanceof Stmt\ClassMethod && !($node->flags & Stmt\Class_::MODIFIER_ABSTRACT)) {
                    return NodeTraverser::REMOVE_NODE;
                }

                // now that all non-abstract methods are remove, we can remove the abstract flag so we get concrete
                // implementations of the abstract methods in our cached classes
                else if ($node instanceof Stmt\ClassMethod) {
                    $node->flags = $node->flags & (~ Stmt\Class_::MODIFIER_ABSTRACT);
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
