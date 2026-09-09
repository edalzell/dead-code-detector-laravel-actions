<?php

namespace Edalzell\DeadCodeDetector\Actions\Tests\Fixtures\Listeners;

class SendShipmentNotification
{
    public function handle(OrderShipped $event): void {}
}
