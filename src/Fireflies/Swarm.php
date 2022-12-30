<?php

namespace App\Fireflies;

class Swarm
{
    /**
     * @var FireflyState[]
     */
    private array $fireflies = [];
    private float $firefliesSyncFactor;

    public function __construct(private readonly SwarmSyncHelper $helper)
    {
    }

    public function init(
        int $fieldSize,
        int $firefliesNum,
        float $firefliesSyncFactor,
        int $firefliesPeriod
    ): void {
        $this->fireflies = [];
        $this->firefliesSyncFactor = $firefliesSyncFactor;
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
                if ($this->helper::areFirefliesInTheSamePlace($firefly, $neighbourFirefly) || !$neighbourFirefly->isShining()) {
                    continue;
                }
                $firefly->syncPhase($this->helper->calculateSync($firefly, $neighbourFirefly, $this->firefliesSyncFactor));
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
}
