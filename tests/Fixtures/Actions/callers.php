<?php

use Edalzell\DeadCodeDetector\Actions\Tests\Fixtures\Actions\SendWelcome;
use Edalzell\DeadCodeDetector\Actions\Tests\Fixtures\Actions\UpdateProfile;

SendWelcome::run('someone@example.com');
UpdateProfile::run('Erin');
