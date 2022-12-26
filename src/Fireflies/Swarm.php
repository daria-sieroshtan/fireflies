<?php

namespace App\Fireflies;

class Swarm
{
    public function __construct($fieldSize, $firefliesNum, $firefliesSyncFactor, $firefliesPeriod)
    {
        $this->step = 0;
    }

    public function step()
    {
        $this->step += 1;
    }

    public function getState()
    {
        return [
            new FireflyState(100, 100, ($this->step % 10) / 10)
        ];
    }
}
