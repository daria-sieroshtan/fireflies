<?php

namespace App\Fireflies;

class FireflyState
{
    private const SHINE_DURATION = 20;
    private const SHINE_STEP = 0.1;

    private float $delayedPhaseAdjustment = 0;

    public function __construct(
        public readonly int $x,
        public readonly int $y,
        private float $phase,
        private readonly int $duration
    ) {
    }

    public function shiftPhase(float $shift): void
    {
        $this->phase = abs(($this->phase + $shift)) % $this->duration;
    }

    public function syncPhase(float $phase): void
    {
        if ($this->isShining()) {
            $this->delayedPhaseAdjustment += $phase;
        } else {
            $phase += $this->delayedPhaseAdjustment;
            $this->delayedPhaseAdjustment = 0;

            if ($this->phase >= $this->duration / 2) {
                $this->shiftPhase($phase);
            } else {
                $this->shiftPhase(-1 * $phase);
            }
        }
    }

    public function getShine(): float
    {
        return round(
            $this->isShining()
            ? ($this->phase <= self::SHINE_DURATION / 2
                ? round($this->phase * self::SHINE_STEP, 1)
                : round((1 - ($this->phase - self::SHINE_DURATION / 2) * self::SHINE_STEP), 1)
            )
            : 0,
        1);
    }

    public function isShining(): bool
    {
        return $this->phase <= self::SHINE_DURATION;
    }
}
