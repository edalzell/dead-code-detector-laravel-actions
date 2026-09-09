<?php

namespace Edalzell\DeadCodeDetector\Actions\Tests\Fixtures\Actions;

use Lorisleiva\Actions\Concerns\AsAction;

class UpdateProfile
{
    use AsAction;

    public function authorize(): bool
    {
        return true;
    }

    public function handle(string $name): void {}

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return ['name' => 'required'];
    }
}
