<?php

declare(strict_types=1);

namespace Knotbane;

use const STR_PAD_LEFT;
use const STR_PAD_RIGHT;

class TextRenderer
{
    public function render(Inspection $inspection): string
    {
        if ($inspection->findings() === []) {
            return $this->emptyReport($inspection);
        }

        return "Cyclomatic complexity hotspots\n\n"
            .$this->table($inspection->findings())
            ."\n"
            .$this->summary($inspection);
    }

    /**
     * @param  non-empty-list<Finding>  $findings
     */
    private function table(array $findings): string
    {
        $scoreWidth = $this->width(
            'CC',
            array_map(
                static fn (Finding $finding): string => (string) $finding->cyclomaticComplexity(),
                $findings,
            ),
        );
        $codeUnitWidth = $this->width(
            'Code unit',
            array_map(
                static fn (Finding $finding): string => $finding->codeUnit(),
                $findings,
            ),
        );
        $fileWidth = $this->width(
            'File',
            array_map(
                static fn (Finding $finding): string => $finding->file(),
                $findings,
            ),
        );

        $output = $this->row('CC', 'Code unit', 'File', $scoreWidth, $codeUnitWidth);
        $output .= sprintf(
            "%s  %s  %s\n",
            str_repeat('─', $scoreWidth),
            str_repeat('─', $codeUnitWidth),
            str_repeat('─', $fileWidth),
        );

        foreach ($findings as $finding) {
            $output .= $this->row(
                (string) $finding->cyclomaticComplexity(),
                $finding->codeUnit(),
                $finding->file(),
                $scoreWidth,
                $codeUnitWidth,
            );
        }

        return $output;
    }

    private function row(
        string $score,
        string $codeUnit,
        string $file,
        int $scoreWidth,
        int $codeUnitWidth,
    ): string {
        return str_pad($score, $scoreWidth, ' ', STR_PAD_LEFT)
            .'  '
            .str_pad($codeUnit, $codeUnitWidth, ' ', STR_PAD_RIGHT)
            .'  '
            .$file
            ."\n";
    }

    /**
     * @param  non-empty-list<string>  $values
     */
    private function width(string $heading, array $values): int
    {
        return max(
            strlen($heading),
            ...array_map(strlen(...), $values),
        );
    }

    private function summary(Inspection $inspection): string
    {
        $hotspots = count($inspection->findings());
        $analyzedFiles = $inspection->analyzedFiles();

        return sprintf(
            "%d %s across %d analyzed %s · CC ≥ %d\n",
            $hotspots,
            $hotspots === 1 ? 'hotspot' : 'hotspots',
            $analyzedFiles,
            $analyzedFiles === 1 ? 'file' : 'files',
            $inspection->minimumReportedComplexity(),
        );
    }

    private function emptyReport(Inspection $inspection): string
    {
        $analyzedFiles = $inspection->analyzedFiles();

        return sprintf(
            "No cyclomatic complexity hotspots.\n\nAnalyzed %d %s · CC ≥ %d\n",
            $analyzedFiles,
            $analyzedFiles === 1 ? 'file' : 'files',
            $inspection->minimumReportedComplexity(),
        );
    }
}
