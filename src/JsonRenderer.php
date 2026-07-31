<?php

declare(strict_types=1);

namespace Knotbane;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;

class JsonRenderer
{
    public function render(Inspection $inspection): string
    {
        $findings = array_map(
            static fn (Finding $finding): array => [
                'cyclomaticComplexity' => $finding->cyclomaticComplexity(),
                'codeUnit' => $finding->codeUnit(),
                'file' => $finding->file(),
            ],
            $inspection->findings(),
        );

        return json_encode(
            [
                'minimumReportedComplexity' => $inspection->minimumReportedComplexity(),
                'analyzedFiles' => $inspection->analyzedFiles(),
                'reportedCodeUnits' => count($findings),
                'findings' => $findings,
            ],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        )."\n";
    }
}
