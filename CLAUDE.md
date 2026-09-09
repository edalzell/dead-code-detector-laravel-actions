This package teaches [shipmonk/dead-code-detector](https://github.com/shipmonk-rnd/dead-code-detector) about [lorisleiva/laravel-actions](https://github.com/lorisleiva/laravel-actions). The Eloquent conventions it also misses live in [edalzell/dead-code-detector-laravel](https://github.com/edalzell/dead-code-detector-laravel); the two packages are independent and neither requires the other.

It is a PHPStan extension, not a Laravel package. No service provider, no config to publish, no `extra.laravel`. `extension.neon` at the root registers every service with its shipmonk tag, and `extra.phpstan.includes` points `phpstan/extension-installer` at it.

`lorisleiva/laravel-actions` is a `suggest`, not a `require`. Every extension matches trait names as strings and never names a class from that package, so they load harmlessly when it is absent. Keep it that way — importing a class from a suggested package puts a fatal error one `composer remove` away.

# Development

Preferences:
* methods that only do one thing
* methods in classes are grouped public, then protected, then private, and sorted alphabetically within each group
* class properties follow the same grouping and sorting
* comments explain why, never what — if a comment restates the line under it, delete the comment

# Testing

A green PHPStan run on real code is not a test. Every extension must be shown to change a verdict.

`tests/Fixtures` holds the code being analysed; `tests/phpstan` holds one config per question. `all.neon` includes the shipped `extension.neon`; each `without-*.neon` registers every service except one. Each test asserts the **exact** set of members reported dead, so dropping a service from `extension.neon` turns a test red naming the fixture that lost its cover.

Adding an extension means adding a fixture that is dead without it and alive with it, plus a `without-*.neon`. Then delete the service from `extension.neon` and watch the test fail before believing it.
