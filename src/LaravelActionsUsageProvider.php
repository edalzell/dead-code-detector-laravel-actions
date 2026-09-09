<?php

namespace Edalzell\DeadCodeDetector\Actions;

use ReflectionClass;
use ReflectionMethod;
use ShipMonk\PHPStan\DeadCode\Provider\ReflectionBasedMemberUsageProvider;
use ShipMonk\PHPStan\DeadCode\Provider\VirtualUsageData;

final class LaravelActionsUsageProvider extends ReflectionBasedMemberUsageProvider
{
    // Every method lorisleiva/laravel-actions resolves by name rather than by a
    // visible call: the four entrypoint adapters, and the validation and response
    // hooks its decorators probe for. Nothing calls these, so all of them read as
    // dead. Kept as a literal list rather than a prefix match so a typo in an
    // action fails loudly instead of being silently marked used.
    private const REFLECTIVELY_CALLED = [
        'asCommand', 'asController', 'asJob', 'asListener',
        'authorize', 'getControllerMiddleware', 'getValidationAttributes',
        'getValidationData', 'getValidationErrorBag', 'getValidationFailure',
        'getValidationMessages', 'getValidationRedirect', 'htmlResponse',
        'jsonResponse', 'prepareForValidation', 'rules', 'withValidator',
    ];

    public function shouldMarkMethodAsUsed(ReflectionMethod $method): ?VirtualUsageData
    {
        if (! in_array($method->getName(), self::REFLECTIVELY_CALLED, true)) {
            return null;
        }

        // Scoped to classes that actually use one of the package's traits, so a
        // `rules()` or `authorize()` on an unrelated class is still reported.
        if (! $this->isAction($method->getDeclaringClass())) {
            return null;
        }

        return VirtualUsageData::withNote('laravel-actions resolves this method by name through its decorators.');
    }

    /**
     * @param  ReflectionClass<object>  $class
     */
    private function isAction(ReflectionClass $class): bool
    {
        foreach ($this->traitsOf($class) as $trait) {
            if (str_starts_with($trait, 'Lorisleiva\\Actions\\Concerns\\')) {
                return true;
            }
        }

        return false;
    }

    /**
     * `getTraitNames()` stops at the first level, but `AsAction` is itself a
     * composition of the individual concerns.
     *
     * @param  ReflectionClass<object>  $class
     * @return list<string>
     */
    private function traitsOf(ReflectionClass $class): array
    {
        $names = [];

        foreach ($class->getTraits() as $trait) {
            $names[] = $trait->getName();
            $names = [...$names, ...$this->traitsOf($trait)];
        }

        return $names;
    }
}
