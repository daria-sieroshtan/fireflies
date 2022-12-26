<?php

namespace App\Fireflies;

class VideoMaker
{
    const OUTPUT_FILE_NAME = "fireflies.mp4";

    public static function makeVideo(int $fps, SwarmRenderer $swarmRenderer, bool $play): string
    {
        $command = sprintf(
            "ffmpeg -f image2 -framerate 25/1 -i %s -r 1 %s 2>&1",
//            $fps,
            sprintf($swarmRenderer->getFrameNamePattern(), "%" . $swarmRenderer::FRAME_NAME_LENGTH . "d"),
            self::OUTPUT_FILE_NAME
        );
        exec($command, $_);

        if ($play) {
            exec(sprintf(
                "ffplay %s -autoexit 2>&1",
                self::OUTPUT_FILE_NAME
            ), $_);
        }

        return self::OUTPUT_FILE_NAME;
    }
}
