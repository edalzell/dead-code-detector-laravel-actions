<?php

namespace Edalzell\DeadCodeDetector\Actions\Tests\Fixtures\Actions;

use Edalzell\DeadCodeDetector\Actions\Tests\Fixtures\Listeners\OrderShipped;
use Lorisleiva\Actions\Concerns\AsAction;

class RecordShipment
{
    use AsAction;

    public function handle(OrderShipped $event): void {}
}
