<?php

namespace App\Fireflies;

class Swarm
{
    /**
     * @var FireflyState[]
     */
    private array $fireflies = [];

    public function __construct(
        int $fieldSize,
        int $firefliesNum,
        private readonly float $firefliesSyncFactor,
        int $firefliesPeriod
    ) {
        for ($i = 1; $i <= $firefliesNum; $i++) {
            $this->fireflies[] = new FireflyState(
                rand(0, $fieldSize),
                rand(0, $fieldSize),
                rand(0, $firefliesPeriod),
                $firefliesPeriod
            );
        }
//        todo: implement re-initiating if too many duplicates
    }

    public function step(): void
    {
        foreach ($this->fireflies as $firefly) {
            $firefly->shiftPhase(1);
            foreach ($this->fireflies as $neighbourFirefly) {
                if (self::areFirefliesInTheSamePlace($firefly, $neighbourFirefly) || !$neighbourFirefly->isShining()) {
                    continue;
                }
                $firefly->syncPhase($this->calculateSync($firefly, $neighbourFirefly));
            }
        }
    }

    /**
     * @return FireflyState[]
     */
    public function getState(): array
    {
        return $this->fireflies;
    }

//    todo: refactor to a helper service
    private static function areFirefliesInTheSamePlace(FireflyState $a, FireflyState $b): bool
    {
        return $a->x === $b->x && $a->y === $b->y;
    }

    private static function calculateDistance(FireflyState $a, FireflyState $b): float
    {
        return sqrt(pow($a->x - $b->x, 2) + pow($a->y - $b->y, 2));
    }

    private function calculateSync(FireflyState $a, FireflyState $b): float
    {
        return 1 / sqrt(self::calculateDistance($a, $b)) * $this->firefliesSyncFactor;
    }
}
