<?php

namespace Edalzell\DeadCodeDetector\Actions;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use ShipMonk\PHPStan\DeadCode\Graph\ClassMethodRef;
use ShipMonk\PHPStan\DeadCode\Graph\ClassMethodUsage;
use ShipMonk\PHPStan\DeadCode\Graph\UsageOrigin;
use ShipMonk\PHPStan\DeadCode\Provider\MemberUsageProvider;

/**
 * `Foo::run()` reaches `Foo::handle()` through AsObject's trait body, which lives
 * in vendor and is never analysed — so the detector never saw the call. Every
 * Action passed anyway, by accident: shipmonk's Laravel provider marks any public
 * `handle*` whose first parameter is class-typed as an auto-discovered listener,
 * which every Action taking a model happened to satisfy. One with a scalar first
 * parameter read as dead however many callers it had, and a genuinely dead one
 * taking a model was invisible. This records the call the trait hides.
 */
final class LaravelActionsRunUsageProvider implements MemberUsageProvider
{
    private const RUNNERS = ['run', 'runIf', 'runUnless'];

    public function getUsages(Node $node, Scope $scope): array
    {
        if (! $node instanceof StaticCall || ! $node->name instanceof Identifier) {
            return [];
        }

        if (! in_array($node->name->toString(), self::RUNNERS, true)) {
            return [];
        }

        $callerType = $node->class instanceof Expr
            ? $scope->getType($node->class)
            : $scope->resolveTypeByName($node->class);

        $usages = [];

        foreach ($callerType->getObjectClassReflections() as $class) {
            if (! $this->isAction($class)) {
                continue;
            }

            $usages[] = new ClassMethodUsage(
                UsageOrigin::createRegular($node, $scope),
                new ClassMethodRef($class->getName(), 'handle', possibleDescendant: true),
            );
        }

        return $usages;
    }

    // hasTraitUse already recurses through composed traits, so this catches
    // AsAction, which is itself a composition of AsObject and the others.
    private function isAction(ClassReflection $class): bool
    {
        return $class->hasTraitUse('Lorisleiva\\Actions\\Concerns\\AsObject');
    }
}
