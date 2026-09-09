<?php

namespace Edalzell\DeadCodeDetector\Actions\Tests\Fixtures\Actions;

use Edalzell\DeadCodeDetector\Actions\Tests\Fixtures\Support\Park;
use Lorisleiva\Actions\Concerns\AsObject;

class ArchivePark
{
    use AsObject;

    public function __construct() {}

    public function handle(Park $park): void {}
}
