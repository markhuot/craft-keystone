<?php

namespace markhuot\craftai\phpstan;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\FunctionTypeSpecifyingExtension;

class ThrowIfTypeSpecifyingExtension implements FunctionTypeSpecifyingExtension, TypeSpecifierAwareExtension
{
    /** @var TypeSpecifier */
    private $typeSpecifier;

    public function isFunctionSupported(
        FunctionReflection $functionReflection,
        FuncCall $node,
        TypeSpecifierContext $context
    ): bool {
        return $functionReflection->getName() === 'throw_if' && $context->null();
    }

    public function specifyTypes(
        FunctionReflection $functionReflection,
        FuncCall $node,
        Scope $scope,
        TypeSpecifierContext $context
    ): SpecifiedTypes {
        if (count($node->getArgs()) < 2) {
            return new SpecifiedTypes();
        }

        // $expr = $node->getArgs()[0]->value;
        // $type = $scope->getType($expr);
        // return $this->typeSpecifier->create($expr, $type, TypeSpecifierContext::createTruthy(), true, $scope);

        return $this->typeSpecifier->specifyTypesInCondition(
            $scope,
            $node->getArgs()[0]->value,
            TypeSpecifierContext::createTruthy()
        );

        // $context = false ? TypeSpecifierContext::createFalsey() : TypeSpecifierContext::createTruthy();
        // return $this->typeSpecifier->specifyTypesInCondition($scope, $node->getArgs()[0]->value, $context);
    }

    public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
    {
        $this->typeSpecifier = $typeSpecifier;
    }
}
