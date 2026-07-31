<?php

declare(strict_types=1);

namespace Knotbane;

use FilesystemIterator;
use Generator;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

class SourceFinder
{
    private string $workingDirectory;

    public function __construct(string $workingDirectory)
    {
        $this->workingDirectory = realpath($workingDirectory) ?: $workingDirectory;
    }

    /**
     * @param  list<string>  $targets
     */
    public function find(array $targets): SourceDiscovery
    {
        $files = [];
        $failures = [];

        foreach ($targets as $target) {
            $discovery = $this->discoveryFor($target);

            foreach ($discovery->files() as $source) {
                $files[$source->path()] = $source;
            }

            array_push($failures, ...$discovery->failures());
        }

        $files = array_values($files);

        usort(
            $files,
            static fn (SourceFile $left, SourceFile $right): int => $left->displayPath() <=> $right->displayPath(),
        );

        return new SourceDiscovery($files, $failures);
    }

    private function discoveryFor(string $target): SourceDiscovery
    {
        $files = [];
        $failures = [];

        try {
            foreach ($this->filesIn($target) as $path) {
                try {
                    $files[] = $this->sourceFile($path);
                } catch (RuntimeException $exception) {
                    $failures[] = $exception->getMessage();
                }
            }
        } catch (RuntimeException $exception) {
            $failures[] = $target.': '.$exception->getMessage();
        }

        return new SourceDiscovery($files, $failures);
    }

    /**
     * @return Generator<int, string>
     */
    private function filesIn(string $target): Generator
    {
        if (is_file($target)) {
            yield $target;

            return;
        }

        if (! is_dir($target)) {
            throw new RuntimeException('Target does not exist or is not a file or directory.');
        }

        yield from $this->phpFilesIn($target);
    }

    /**
     * @return Generator<int, string>
     */
    private function phpFilesIn(string $directoryPath): Generator
    {
        if (! is_readable($directoryPath)) {
            throw new RuntimeException('Directory is not readable.');
        }

        $directory = new RecursiveDirectoryIterator(
            $directoryPath,
            FilesystemIterator::SKIP_DOTS,
        );
        $filtered = new RecursiveCallbackFilterIterator(
            $directory,
            $this->keepsEntry(...),
        );

        foreach (new RecursiveIteratorIterator($filtered) as $entry) {
            if (! $this->isPhpFile($entry)) {
                continue;
            }

            yield $entry->getPathname();
        }
    }

    private function isPhpFile(SplFileInfo $entry): bool
    {
        return $entry->isFile()
            && str_ends_with(strtolower($entry->getFilename()), '.php');
    }

    private function keepsEntry(SplFileInfo $entry): bool
    {
        if (! $entry->isDir()) {
            return true;
        }

        return ! $entry->isLink() && $entry->getFilename() !== 'vendor';
    }

    private function sourceFile(string $path): SourceFile
    {
        $canonicalPath = realpath($path);

        if ($canonicalPath === false || ! is_readable($canonicalPath)) {
            throw new RuntimeException('Source file is not readable: '.$path);
        }

        return new SourceFile(
            $canonicalPath,
            $this->displayPath($canonicalPath),
        );
    }

    private function displayPath(string $path): string
    {
        $prefix = $this->workingDirectory.DIRECTORY_SEPARATOR;

        if (str_starts_with($path, $prefix)) {
            $path = substr($path, strlen($prefix));
        }

        return str_replace(DIRECTORY_SEPARATOR, '/', $path);
    }
}
