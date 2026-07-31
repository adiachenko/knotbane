<?php

declare(strict_types=1);

it('reports hotspots from multiple targets once in descending order', function () {
    $result = runKnotbane(
        '--min-cc=5',
        'tests/Fixtures/Targets/high.php',
        'tests/Fixtures/Targets',
    );

    expect($result['exitCode'])->toBe(0)
        ->and($result['stderr'])->toBe('')
        ->and($result['stdout'])->toBe(
            <<<'OUTPUT'
            Cyclomatic complexity hotspots

            CC  Code unit                         File
            ──  ────────────────────────────────  ─────────────────────────────────
             7  Knotbane\Tests\Fixtures\severe    tests/Fixtures/Targets/high.php
             5  Knotbane\Tests\Fixtures\moderate  tests/Fixtures/Targets/medium.php

            2 hotspots across 3 analyzed files · CC ≥ 5

            OUTPUT,
        );
});

it('emits machine-readable findings', function () {
    $result = runKnotbane(
        '--json',
        'tests/Fixtures/Targets/medium.php',
    );

    expect($result['exitCode'])->toBe(0)
        ->and($result['stderr'])->toBe('')
        ->and(json_decode($result['stdout'], true, flags: JSON_THROW_ON_ERROR))->toBe([
            'minimumReportedComplexity' => 5,
            'analyzedFiles' => 1,
            'reportedCodeUnits' => 1,
            'findings' => [
                [
                    'cyclomaticComplexity' => 5,
                    'codeUnit' => 'Knotbane\Tests\Fixtures\moderate',
                    'file' => 'tests/Fixtures/Targets/medium.php',
                ],
            ],
        ]);
});

it('reports findings and failure when another target is missing', function () {
    $result = runKnotbane(
        '--min-cc',
        '7',
        'tests/Fixtures/Targets/high.php',
        'tests/Fixtures/missing.php',
    );

    expect($result['exitCode'])->toBe(1)
        ->and($result['stdout'])
        ->toContain(
            'Knotbane\Tests\Fixtures\severe',
            'tests/Fixtures/Targets/high.php',
            '1 hotspot across 1 analyzed file',
        )
        ->and($result['stderr'])
        ->toContain(
            'tests/Fixtures/missing.php:',
            'Target does not exist or is not a file or directory.',
        );
});

it('succeeds with an empty report', function () {
    $result = runKnotbane('tests/Fixtures/Targets/low.php');

    expect($result['exitCode'])->toBe(0)
        ->and($result['stderr'])->toBe('')
        ->and($result['stdout'])->toBe(
            <<<'OUTPUT'
            No cyclomatic complexity hotspots.

            Analyzed 1 file · CC ≥ 5

            OUTPUT,
        );
});

it('requires at least one target', function () {
    $result = runKnotbane();

    expect($result['exitCode'])->toBe(2)
        ->and($result['stdout'])->toBe('')
        ->and($result['stderr'])->toContain(
            'At least one path is required.',
            'Usage: knotbane',
        );
});

it('requires a positive minimum reported complexity', function () {
    $result = runKnotbane(
        '--min-cc=0',
        'tests/Fixtures/Targets/high.php',
    );

    expect($result['exitCode'])->toBe(2)
        ->and($result['stdout'])->toBe('')
        ->and($result['stderr'])->toContain('--min-cc must be a positive integer.');
});
