<?php

namespace Edalzell\DeadCodeDetector\Actions\Tests;

use RuntimeException;

final class Analysis
{
    /**
     * Member names shipmonk reports as dead when the fixtures are analysed under
     * the named config in tests/phpstan, shortened to `Class::member`.
     *
     * @return list<string>
     */
    public static function deadMembers(string $config): array
    {
        $root = dirname(__DIR__);

        $process = proc_open(
            [
                PHP_BINARY,
                $root.'/vendor/bin/phpstan',
                'analyse',
                '--configuration='.$root.'/tests/phpstan/'.$config.'.neon',
                '--error-format=json',
                '--no-progress',
                '--no-ansi',
            ],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $root,
        );

        if ($process === false) {
            throw new RuntimeException('Could not start PHPStan.');
        }

        $stdout = (string) stream_get_contents($pipes[1]);
        $stderr = (string) stream_get_contents($pipes[2]);

        array_map(fclose(...), $pipes);
        proc_close($process);

        $result = json_decode($stdout, true);

        if (! is_array($result) || ! isset($result['files'])) {
            throw new RuntimeException("PHPStan produced no report for {$config}.\n{$stdout}\n{$stderr}");
        }

        $dead = [];

        /** @var array<string, array{messages: list<array{message: string}>}> $files */
        $files = $result['files'];

        foreach ($files as $file) {
            foreach ($file['messages'] as $message) {
                if (preg_match('/^Unused \S+\\\\(\w+::\w+)/', $message['message'], $matches) === 1) {
                    $dead[] = $matches[1];
                }
            }
        }

        sort($dead);

        return $dead;
    }
}
