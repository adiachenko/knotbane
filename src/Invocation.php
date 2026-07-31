<?php

declare(strict_types=1);

namespace Knotbane;

readonly class Invocation
{
    public const DEFAULT_MINIMUM_REPORTED_COMPLEXITY = 5;

    /**
     * @param  list<string>  $targets
     */
    public function __construct(
        private array $targets,
        private int $minimumReportedComplexity,
        private bool $json,
        private bool $help,
    ) {}

    /**
     * @return list<string>
     */
    public function targets(): array
    {
        return $this->targets;
    }

    public function minimumReportedComplexity(): int
    {
        return $this->minimumReportedComplexity;
    }

    public function usesJson(): bool
    {
        return $this->json;
    }

    public function showsHelp(): bool
    {
        return $this->help;
    }
}
