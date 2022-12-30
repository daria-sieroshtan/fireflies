<?php

namespace App\Fireflies;

class SwarmSyncHelper
{
    public static function areFirefliesInTheSamePlace(FireflyState $a, FireflyState $b): bool
    {
        return $a->x === $b->x && $a->y === $b->y;
    }

    public static function calculateDistance(FireflyState $a, FireflyState $b): float
    {
        return sqrt(pow($a->x - $b->x, 2) + pow($a->y - $b->y, 2));
    }

    public function calculateSync(FireflyState $a, FireflyState $b, float $firefliesSyncFactor): float
    {
        return $firefliesSyncFactor / sqrt(self::calculateDistance($a, $b));
    }
}
