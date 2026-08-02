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

it('runs from Composer installations through vendor proxies and CPX', function () {
    $projectRoot = dirname(__DIR__);
    $installation = sys_get_temp_dir().'/knotbane-package-'.bin2hex(random_bytes(8));
    $packageRoot = $installation.'/vendor/adiachenko/knotbane';
    $binary = $packageRoot.'/bin/knotbane';
    $autoload = $installation.'/vendor/autoload.php';

    mkdir(dirname($binary), recursive: true);
    copy($projectRoot.'/bin/knotbane', $binary);
    file_put_contents(
        $autoload,
        '<?php require '.var_export($projectRoot.'/vendor/autoload.php', true).';',
    );

    try {
        $proxyResult = runProcess(
            [
                PHP_BINARY,
                '-r',
                <<<'PHP'
                $GLOBALS['_composer_autoload_path'] = $argv[1];
                $binary = $argv[2];
                $argv = [$binary, ...array_slice($argv, 3)];
                require $binary;
                PHP,
                $projectRoot.'/vendor/autoload.php',
                $binary,
                '--json',
                $projectRoot.'/tests/Fixtures/Targets/low.php',
            ],
            $projectRoot,
        );
        $cpxResult = runProcess(
            [
                PHP_BINARY,
                $binary,
                '--json',
                $projectRoot.'/tests/Fixtures/Targets/low.php',
            ],
            $projectRoot,
        );
    } finally {
        unlink($binary);
        unlink($autoload);
        rmdir(dirname($binary));
        rmdir($packageRoot);
        rmdir(dirname($packageRoot));
        rmdir(dirname($packageRoot, 2));
        rmdir($installation);
    }

    expect($proxyResult['exitCode'])->toBe(0)
        ->and($proxyResult['stderr'])->toBe('')
        ->and(json_decode($proxyResult['stdout'], true, flags: JSON_THROW_ON_ERROR))->toMatchArray([
            'analyzedFiles' => 1,
            'reportedCodeUnits' => 0,
        ])
        ->and($cpxResult['exitCode'])->toBe(0)
        ->and($cpxResult['stderr'])->toBe('')
        ->and(json_decode($cpxResult['stdout'], true, flags: JSON_THROW_ON_ERROR))->toMatchArray([
            'analyzedFiles' => 1,
            'reportedCodeUnits' => 0,
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
