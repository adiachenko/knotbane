<?php

declare(strict_types=1);

namespace Knotbane\Tests\Fixtures;

function severe(
    bool $first,
    bool $second,
    bool $third,
    bool $fourth,
    bool $fifth,
    bool $sixth,
): bool {
    if ($first) {
        return true;
    }

    if ($second) {
        return true;
    }

    if ($third) {
        return true;
    }

    if ($fourth) {
        return true;
    }

    if ($fifth) {
        return true;
    }

    if ($sixth) {
        return true;
    }

    return false;
}

function routine(): bool
{
    return true;
}
