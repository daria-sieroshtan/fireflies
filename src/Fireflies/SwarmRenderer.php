<?php

namespace App\Fireflies;


use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

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
        $fs = new Filesystem();
        $fs->remove(
            Finder::create()
                ->files()
                ->in($this->rootDir)
                ->name(str_replace("%s", "*", self::FRAME_FILE_NAME_PATTERN))
        );
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
        $radius = $firefly->getShine() * 100;
        $intensity = 0;
        while ($radius > 0) {
            $component = dechex(hexdec("ff")*$intensity);
            if (strlen($component) === 1) {
                $component = "0" . $component;
            }
            $img->circle(
                $radius,
                $firefly->getX(),
                $firefly->getY(),
                function ($draw) use ($component) {
                    $draw->background(sprintf('#%s%s%s', $component, $component, $component));
                }
            );
            $radius = $radius - 5;
            $intensity = $intensity + 0.05;
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
