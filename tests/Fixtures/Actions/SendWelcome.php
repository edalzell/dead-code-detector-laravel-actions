<?php

namespace Edalzell\DeadCodeDetector\Actions\Tests\Fixtures\Actions;

use Lorisleiva\Actions\Concerns\AsObject;

class SendWelcome
{
    use AsObject;

    public function handle(string $email): void {}
}
