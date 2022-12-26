<?php

namespace App\Fireflies;

class SwarmRenderer
{
    public function renderSwarm(Swarm $swarm): void
    {
        usleep(5000);
        $swarm->getState();
    }

    public function cleanUp(): void
    {

    }
}
