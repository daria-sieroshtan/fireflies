<?php

namespace App\Fireflies;

class VideoMaker
{
    const OUTPUT_FILE_NAME = "fireflies.gif";

    public static function makeVideo(int $fps, SwarmRenderer $swarmRenderer): string
    {
        @unlink(self::OUTPUT_FILE_NAME);
        $command = sprintf(
            'ffmpeg -f image2 -framerate %s -i %s %s 2>&1',
            $fps,
            sprintf($swarmRenderer->getFrameNamePattern(), "%" . $swarmRenderer::FRAME_NAME_LENGTH . "d"),
            self::OUTPUT_FILE_NAME
        );
        exec($command, $_);

        return self::OUTPUT_FILE_NAME;
    }
}
