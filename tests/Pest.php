<?php

declare(strict_types=1);

/**
 * @return array{exitCode: int, stdout: string, stderr: string}
 */
function runKnotbane(string ...$arguments): array
{
    $projectRoot = dirname(__DIR__);
    $pipes = [];
    $process = proc_open(
        [PHP_BINARY, $projectRoot.'/bin/knotbane', ...$arguments],
        [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ],
        $pipes,
        $projectRoot,
    );

    if (! is_resource($process)) {
        throw new RuntimeException('Unable to start Knotbane.');
    }

    fclose($pipes[0]);

    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);

    fclose($pipes[1]);
    fclose($pipes[2]);

    $exitCode = proc_close($process);

    if (! is_string($stdout) || ! is_string($stderr)) {
        throw new RuntimeException('Unable to read Knotbane output.');
    }

    return [
        'exitCode' => $exitCode,
        'stdout' => $stdout,
        'stderr' => $stderr,
    ];
}
