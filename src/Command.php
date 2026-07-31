<?php

declare(strict_types=1);

namespace Knotbane;

class Command
{
    public function run(array $arguments): int
    {
        try {
            $invocation = (new InvocationParser)->parse(array_slice($arguments, 1));
        } catch (InvalidInvocation $exception) {
            fwrite(STDERR, $exception->getMessage()."\n\n".$this->usage());

            return 2;
        }

        if ($invocation->showsHelp()) {
            fwrite(STDOUT, $this->usage());

            return 0;
        }

        $sources = (new SourceFinder($this->workingDirectory()))->find($invocation->targets());
        $inspection = (new Inspector)->inspect(
            $sources,
            $invocation->minimumReportedComplexity(),
        );

        $this->writeReport($inspection, $invocation);
        $this->writeFailures($inspection);

        return $inspection->failed() ? 1 : 0;
    }

    private function workingDirectory(): string
    {
        return getcwd() ?: '.';
    }

    private function writeReport(Inspection $inspection, Invocation $invocation): void
    {
        if ($invocation->usesJson()) {
            fwrite(STDOUT, (new JsonRenderer)->render($inspection));

            return;
        }

        fwrite(STDOUT, (new TextRenderer)->render($inspection));
    }

    private function writeFailures(Inspection $inspection): void
    {
        foreach ($inspection->failures() as $failure) {
            fwrite(STDERR, $failure."\n");
        }
    }

    private function usage(): string
    {
        return sprintf(
            <<<'USAGE'
        Usage: knotbane [--min-cc <value>] [--json] <path> [<path> ...]

        Find cyclomatic complexity hotspots in PHP files and directories.

        Options:
          --min-cc <value>  Report code units with CC at or above this value (default: %d)
          --json            Emit a machine-readable JSON report
          -h, --help        Show this help

        USAGE,
            Invocation::DEFAULT_MINIMUM_REPORTED_COMPLEXITY,
        );
    }
}
