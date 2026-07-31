<?php

declare(strict_types=1);

namespace Knotbane;

readonly class Inspection
{
    /**
     * @param  list<Finding>  $findings
     * @param  list<string>  $failures
     */
    public function __construct(
        private int $analyzedFiles,
        private int $minimumReportedComplexity,
        private array $findings,
        private array $failures,
    ) {}

    public function analyzedFiles(): int
    {
        return $this->analyzedFiles;
    }

    public function minimumReportedComplexity(): int
    {
        return $this->minimumReportedComplexity;
    }

    /**
     * @return list<Finding>
     */
    public function findings(): array
    {
        return $this->findings;
    }

    /**
     * @return list<string>
     */
    public function failures(): array
    {
        return $this->failures;
    }

    public function failed(): bool
    {
        return $this->failures !== [];
    }
}
