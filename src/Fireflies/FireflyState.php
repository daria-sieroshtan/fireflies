<?php

namespace App\Fireflies;

class FireflyState
{
    private $x;
    private $y;
    private $shine;

    public function __construct($x, $y, $shine)
    {
        $this->x = $x;
        $this->y = $y;
        $this->shine = $shine;
    }

    public function getX()
    {
        return $this->x;
    }

    public function getY()
    {
        return $this->y;
    }

    public function getShine()
    {
        return $this->shine;
    }
}
