<?php

declare(strict_types=1);

namespace Knotbane;

class InvocationParser
{
    /**
     * @var list<string>
     */
    private array $arguments = [];

    /**
     * @var list<string>
     */
    private array $targets = [];

    private int $minimumReportedComplexity = Invocation::DEFAULT_MINIMUM_REPORTED_COMPLEXITY;

    private bool $json = false;

    private bool $help = false;

    private bool $acceptOptions = true;

    /**
     * @param  list<string>  $arguments
     */
    public function parse(array $arguments): Invocation
    {
        $this->arguments = $arguments;

        while ($this->arguments !== []) {
            $this->read(array_shift($this->arguments));
        }

        if (! $this->help && $this->targets === []) {
            throw new InvalidInvocation('At least one path is required.');
        }

        return new Invocation(
            $this->targets,
            $this->minimumReportedComplexity,
            $this->json,
            $this->help,
        );
    }

    private function read(string $argument): void
    {
        if ($this->isTarget($argument)) {
            $this->targets[] = $argument;

            return;
        }

        if ($this->readOption($argument)) {
            return;
        }

        throw new InvalidInvocation('Unknown option: '.$argument);
    }

    private function readOption(string $argument): bool
    {
        if ($argument === '--') {
            $this->acceptOptions = false;

            return true;
        }

        return $this->readFlag($argument) || $this->readMinimum($argument);
    }

    private function isTarget(string $argument): bool
    {
        if (! $this->acceptOptions) {
            return true;
        }

        return ! str_starts_with($argument, '-');
    }

    private function readFlag(string $argument): bool
    {
        if ($argument === '--json') {
            $this->json = true;

            return true;
        }

        if (in_array($argument, ['-h', '--help'], true)) {
            $this->help = true;

            return true;
        }

        return false;
    }

    private function readMinimum(string $argument): bool
    {
        if ($argument === '--min-cc') {
            $this->minimumReportedComplexity = $this->parseMinimum(array_shift($this->arguments));

            return true;
        }

        if (! str_starts_with($argument, '--min-cc=')) {
            return false;
        }

        $this->minimumReportedComplexity = $this->parseMinimum(substr($argument, 9));

        return true;
    }

    private function parseMinimum(?string $value): int
    {
        if ($value === null) {
            throw new InvalidInvocation('--min-cc must be a positive integer.');
        }

        if (! ctype_digit($value)) {
            throw new InvalidInvocation('--min-cc must be a positive integer.');
        }

        $minimum = (int) $value;

        if ($minimum < 1) {
            throw new InvalidInvocation('--min-cc must be a positive integer.');
        }

        return $minimum;
    }
}
