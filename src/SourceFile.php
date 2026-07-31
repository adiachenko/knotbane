<?php

declare(strict_types=1);

namespace Knotbane;

readonly class SourceFile
{
    public function __construct(
        private string $path,
        private string $displayPath,
    ) {}

    public function path(): string
    {
        return $this->path;
    }

    public function displayPath(): string
    {
        return $this->displayPath;
    }
}
