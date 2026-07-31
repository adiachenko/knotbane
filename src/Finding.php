<?php

declare(strict_types=1);

namespace Knotbane;

readonly class Finding
{
    public function __construct(
        private int $cyclomaticComplexity,
        private string $codeUnit,
        private string $file,
    ) {}

    public function cyclomaticComplexity(): int
    {
        return $this->cyclomaticComplexity;
    }

    public function codeUnit(): string
    {
        return $this->codeUnit;
    }

    public function file(): string
    {
        return $this->file;
    }
}
