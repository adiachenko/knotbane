<?php

declare(strict_types=1);

namespace Knotbane\Tests\Fixtures;

function moderate(bool $first, bool $second, bool $third, bool $fourth): bool
{
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

    return false;
}
