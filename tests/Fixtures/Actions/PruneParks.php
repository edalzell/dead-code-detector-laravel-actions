<?php

namespace Edalzell\DeadCodeDetector\Actions\Tests\Fixtures\Actions;

use Lorisleiva\Actions\Concerns\AsCommand;

class PruneParks
{
    use AsCommand;

    public string $commandSignature = 'parks:prune';

    public function handle(): void {}
}
