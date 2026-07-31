<?php

declare(strict_types=1);

namespace Knotbane;

readonly class SourceDiscovery
{
    /**
     * @param  list<SourceFile>  $files
     * @param  list<string>  $failures
     */
    public function __construct(
        private array $files,
        private array $failures,
    ) {}

    /**
     * @return list<SourceFile>
     */
    public function files(): array
    {
        return $this->files;
    }

    /**
     * @return list<string>
     */
    public function failures(): array
    {
        return $this->failures;
    }
}
