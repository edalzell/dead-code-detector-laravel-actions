<?php

use Edalzell\DeadCodeDetector\Actions\Tests\Analysis;

it('reports only the uncalled Action when every extension is registered', function () {
    expect(Analysis::deadMembers('all'))->toBe(['ArchivePark::handle']);
});

it('reports reflectively called Action hooks without LaravelActionsUsageProvider', function () {
    expect(Analysis::deadMembers('without-laravel-actions'))
        ->toBe(['ArchivePark::handle', 'UpdateProfile::authorize', 'UpdateProfile::rules']);
});

it('reports handle() of Actions called via run() without LaravelActionsRunUsageProvider', function () {
    expect(Analysis::deadMembers('without-laravel-actions-run'))
        ->toBe(['ArchivePark::handle', 'SendWelcome::handle', 'UpdateProfile::handle']);
});

it('hides the uncalled Action behind the listener heuristic without ActionListenerUsageExcluder', function () {
    expect(Analysis::deadMembers('without-action-listener-excluder'))->toBe([]);
});
