<?php

namespace App\Fireflies;


use Intervention\Image\Image;
use Intervention\Image\ImageManager;

class SwarmRenderer
{
    const BG_IMAGE_NAME = "bg.png";
    const FRAME_FILE_NAME_PATTERN = "frame-%s.png";
    const FRAME_NAME_LENGTH = 5;

    private string $rootDir;
    private int $fieldSize;
    private int $framesDumped;
    private ImageManager $imageManager;

    public function __construct(int $fieldSize, string $rootDir = null)
    {
        if ($rootDir === null) {
            $rootDir = getcwd() . DIRECTORY_SEPARATOR . "img";
        }
        $this->rootDir = $rootDir;
        $this->fieldSize = $fieldSize;
        $this->framesDumped = 0;
        $this->imageManager = new ImageManager();
    }

    public function renderSwarm(Swarm $swarm): void
    {
        $img = $this->getBaseImage();
        $this->addSwarm($img, $swarm);
        $this->dumpFrame($img);
    }

    public function cleanUp(): void
    {
        // todo rm frames
    }

    public function getFrameNamePattern(): string
    {
        return $this->rootDir . DIRECTORY_SEPARATOR . self::FRAME_FILE_NAME_PATTERN;
    }

    private function getBaseImage(): Image
    {
        $img = $this->imageManager->make($this->rootDir . DIRECTORY_SEPARATOR . self::BG_IMAGE_NAME);
        // todo filter?
        $img->resize($this->fieldSize, $this->fieldSize);
        return $img;
    }

    private function addSwarm(Image $img, Swarm $swarm): void
    {
        foreach ($swarm->getState() as $fireflyState) {
            $this->addFirefly($img, $fireflyState);
        }
    }

    private function addFirefly(Image $img, FireflyState $firefly): void
    {
        // todo
//        $img->insert('public/watermark.png');
        if ($firefly->getShine() > 0.5) {
            $img->circle(
                30,
                $firefly->getX(),
                $firefly->getY(),
                function ($draw) {
                    $draw->background('#fff');
                    $draw->border(1, '#000');
                }
            );
        }
    }

    private function dumpFrame($img): void
    {
        $generatedFileName = sprintf(
            self::FRAME_FILE_NAME_PATTERN,
            str_pad($this->framesDumped, self::FRAME_NAME_LENGTH, "0", STR_PAD_LEFT)
        );
        $img->save($this->rootDir . DIRECTORY_SEPARATOR . $generatedFileName);
        $this->framesDumped += 1;
    }
}
