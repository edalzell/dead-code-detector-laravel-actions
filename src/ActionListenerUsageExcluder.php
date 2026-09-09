<?php

namespace Edalzell\DeadCodeDetector\Actions;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use ShipMonk\PHPStan\DeadCode\Excluder\MemberUsageExcluder;
use ShipMonk\PHPStan\DeadCode\Graph\ClassMemberUsage;

final class ActionListenerUsageExcluder implements MemberUsageExcluder
{
    // The exact note shipmonk's LaravelUsageProvider attaches to the usage its
    // listener heuristic invents. Matching the note rather than the method shape
    // subtracts only that provider's guess and leaves every real call intact.
    private const LISTENER_NOTE = 'Laravel auto-discovered event listener method';

    private const OBJECT_TRAIT = 'Lorisleiva\\Actions\\Concerns\\AsObject';

    private const LISTENER_TRAIT = 'Lorisleiva\\Actions\\Concerns\\AsListener';

    public function __construct(private readonly ReflectionProvider $reflectionProvider) {}

    public function getIdentifier(): string
    {
        return 'laravelActionsListenerHeuristic';
    }

    public function shouldExclude(ClassMemberUsage $usage, Node $node, Scope $scope): bool
    {
        if ($usage->getOrigin()->getNote() !== self::LISTENER_NOTE) {
            return false;
        }

        // The heuristic tags the constructor of anything it took for a listener too.
        // For an Action that conclusion still holds — laravel-actions resolves the
        // class through the container — so only the `handle`/`__invoke` claim is wrong.
        if ($usage->getMemberRef()->getMemberName() === '__construct') {
            return false;
        }

        $className = $usage->getMemberRef()->getClassName();

        if ($className === null || ! $this->reflectionProvider->hasClass($className)) {
            return false;
        }

        $class = $this->reflectionProvider->getClass($className);

        return $class->hasTraitUse(self::OBJECT_TRAIT)
            && ! $class->hasTraitUse(self::LISTENER_TRAIT);
    }
}
