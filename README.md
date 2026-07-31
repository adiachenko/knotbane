# Knotbane

[![Tests](https://github.com/adiachenko/knotbane/actions/workflows/tests.yml/badge.svg)](https://github.com/adiachenko/knotbane/actions/workflows/tests.yml)

Knotbane finds cyclomatic complexity hotspots in targeted PHP code. It wraps [`sebastian/complexity`](https://github.com/sebastianbergmann/complexity) in a small Composer-installed CLI.

Complexity is a review signal, not a quality verdict. Knotbane identifies where to look; it does not fail a run merely because a code unit has a high score.

## Requirements

PHP >= 8.4 (the package follows a rolling support policy covering the latest two PHP minor versions).

## Installation

Install Knotbane as a development dependency:

```shell
composer require --dev adiachenko/knotbane
```

For one-off use without adding Knotbane to your project, run it through [CPX](https://github.com/laravel/cpx):

```shell
cpx adiachenko/knotbane src
```

## Usage

Pass one or more PHP files or directories:

```shell
vendor/bin/knotbane src app/Http/Controllers/CheckoutController.php
```

Knotbane combines all targets into one report and orders code units from highest to lowest complexity. Directory targets are scanned recursively for PHP files; dependency directories named `vendor` and symlinked directories are skipped.

By default, the report includes code units with cyclomatic complexity 5 or greater. Set a different minimum when you need a narrower or broader view:

```shell
vendor/bin/knotbane --min-cc=10 src
vendor/bin/knotbane --min-cc=1 src/Checkout.php
```

Use JSON when another tool or coding agent will consume the findings:

```shell
vendor/bin/knotbane --json src packages/Billing/src
```

## Exit status

- `0`: analysis completed, including when no code units meet the reporting cutoff.
- `1`: at least one target or source could not be analyzed. Findings from valid sources are still reported.
- `2`: the command invocation is invalid.

Diagnostics are written to standard error, leaving standard output usable as either a text or JSON report.

## Code improvement workflow

Run Knotbane on the code relevant to the current task, inspect the highest-ranked code units, and decide whether their branching is genuinely difficult to understand. After refactoring, rerun the same command and compare the ranking.

Reducing the score is not the objective by itself. Arbitrary method extraction or added indirection can lower cyclomatic complexity while making the code worse.

## Agent skill

Knotbane includes an agent skill that targets cyclomatic complexity of 4 or lower, treats 6 as the usual maximum, and rejects refactors that only move branching elsewhere.

Just ask Codex or Claude Code to install this skill from the repository, or run:

```shell
npx skills add adiachenko/knotbane --global
```

Then invoke `/knotbane` on the PHP code you want measured and simplified.

## Contributing

Install dependencies, run the command-level test suite, and format the code:

```shell
composer install
composer test
composer format
```
