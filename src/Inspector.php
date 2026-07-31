<?php

declare(strict_types=1);

namespace Knotbane;

use SebastianBergmann\Complexity\Calculator;
use SebastianBergmann\Complexity\Complexity;
use SebastianBergmann\Complexity\RuntimeException;

class Inspector
{
    private readonly Calculator $calculator;

    public function __construct()
    {
        $this->calculator = new Calculator;
    }

    public function inspect(SourceDiscovery $sources, int $minimumReportedComplexity): Inspection
    {
        $analyzedFiles = 0;
        $findings = [];
        $failures = $sources->failures();

        foreach ($sources->files() as $source) {
            try {
                $sourceFindings = $this->findingsIn($source, $minimumReportedComplexity);
            } catch (RuntimeException $exception) {
                $failures[] = $source->displayPath().': '.$exception->getMessage();

                continue;
            }

            $analyzedFiles++;
            array_push($findings, ...$sourceFindings);
        }

        usort(
            $findings,
            $this->compareFindings(...),
        );

        return new Inspection(
            $analyzedFiles,
            $minimumReportedComplexity,
            $findings,
            $failures,
        );
    }

    private function compareFindings(Finding $left, Finding $right): int
    {
        return $right->cyclomaticComplexity() <=> $left->cyclomaticComplexity()
            ?: strcmp($left->file(), $right->file())
            ?: strcmp($left->codeUnit(), $right->codeUnit());
    }

    /**
     * @return list<Finding>
     */
    private function findingsIn(SourceFile $source, int $minimumReportedComplexity): array
    {
        $findings = [];

        foreach ($this->calculator->calculateForSourceFile($source->path()) as $complexity) {
            if ($complexity->cyclomaticComplexity() < $minimumReportedComplexity) {
                continue;
            }

            $findings[] = $this->finding($complexity, $source);
        }

        return $findings;
    }

    private function finding(Complexity $complexity, SourceFile $source): Finding
    {
        return new Finding(
            $complexity->cyclomaticComplexity(),
            $complexity->name(),
            $source->displayPath(),
        );
    }
}
