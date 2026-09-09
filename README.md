[![Latest Version on Packagist](https://img.shields.io/packagist/v/edalzell/dead-code-detector-laravel-actions.svg?style=flat-square)](https://packagist.org/packages/edalzell/dead-code-detector-laravel-actions)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/edalzell/dead-code-detector-laravel-actions/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/edalzell/dead-code-detector-laravel-actions/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/edalzell/dead-code-detector-laravel-actions/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/edalzell/dead-code-detector-laravel-actions/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/edalzell/dead-code-detector-laravel-actions.svg?style=flat-square)](https://packagist.org/packages/edalzell/dead-code-detector-laravel-actions)

Two usage providers and one usage excluder for [shipmonk/dead-code-detector](https://github.com/shipmonk-rnd/dead-code-detector), covering [lorisleiva/laravel-actions](https://github.com/lorisleiva/laravel-actions).

This is a PHPStan extension, not a Laravel package. There is no service provider and nothing to publish.

For the Eloquent conventions shipmonk misses — trait hooks and `#[Scope]` attributes — see [edalzell/dead-code-detector-laravel](https://github.com/edalzell/dead-code-detector-laravel).

## Installation

```bash
composer require --dev edalzell/dead-code-detector-laravel-actions
```

With `phpstan/extension-installer` the extension registers itself. Without it, add one line:

```neon
includes:
    - vendor/shipmonk/dead-code-detector/rules.neon
    - vendor/edalzell/dead-code-detector-laravel-actions/extension.neon
```

`lorisleiva/laravel-actions` is a suggestion, not a requirement. All three extensions match on trait names as strings and never reference a class from that package, so they load and do nothing when it is absent.

## What each extension does

**`LaravelActionsUsageProvider`** — laravel-actions resolves a set of methods by name through its decorators: the four entrypoint adapters, and the validation and response hooks. Nothing calls them, so all of them read as dead. The list is literal rather than a prefix match, so a typo in an Action still fails loudly, and it is scoped to classes actually using one of the package's traits, so a `rules()` on an unrelated class is still reported.

**`LaravelActionsRunUsageProvider`** — `Foo::run()` reaches `Foo::handle()` through `AsObject`'s trait body, which lives in vendor and is never analysed, so the detector never sees the call. Every Action used to pass anyway, by accident: shipmonk's Laravel provider marks any public `handle*` whose first parameter is class-typed as an auto-discovered listener, which every Action taking a model happened to satisfy. One with a scalar first parameter read as dead however many callers it had. This records the call the trait hides, for `run`, `runIf` and `runUnless`.

**`ActionListenerUsageExcluder`** — the flip side of that heuristic. Because it marks any class-typed-first-parameter `handle` as a listener, a genuinely dead Action taking a model was invisible: one shipped with no caller for a whole phase and nothing reported it. This subtracts that one invented usage — matched by the note shipmonk attaches to it, so real calls are untouched — for classes using `AsObject` but not `AsListener`. An Action that really is a listener keeps the heuristic, and so does the constructor, which laravel-actions resolves through the container either way.

## Testing

```bash
composer test
```

The suite runs PHPStan over `tests/Fixtures` four times: once with the shipped `extension.neon`, and once per extension with that one service left out. Each run asserts the exact set of members reported dead, so removing any extension turns a test red with a named fixture rather than quietly changing nothing.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
