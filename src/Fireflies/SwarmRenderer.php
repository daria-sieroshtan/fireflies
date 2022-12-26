<?php

namespace App\Fireflies;


use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class SwarmRenderer
{
    const BG_IMAGE_NAME = "bg.png";
    const FRAME_FILE_NAME_PATTERN = "frame-%s.png";
    const FRAME_NAME_LENGTH = 5;
    const SHINING = "#ff0";
    const DEFAULT_SHINE_RADIUS = 50;
    const DEFAULT_SHINE_STEP = 0.05;

    private string $rootDir;
    private int $fieldSize;
    private int $framesDumped;
    private ImageManager $imageManager;
    private int $shineRadius;
    private float $shineStep;

    public function __construct(
        int $fieldSize,
        int $shineRadius = self::DEFAULT_SHINE_RADIUS,
        float $shineStep = self::DEFAULT_SHINE_STEP,
        string $rootDir = null
    ) {
        if ($rootDir === null) {
            $rootDir = getcwd() . DIRECTORY_SEPARATOR . "img";
        }
        $this->rootDir = $rootDir;
        $this->fieldSize = $fieldSize;
        $this->framesDumped = 0;
        $this->shineRadius = $shineRadius;
        $this->shineStep = $shineStep;
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
        $img->colorize(-5, 0, 5);
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
        $shining = $this->imageManager->canvas($this->fieldSize, $this->fieldSize);
        $shining->fill(self::SHINING);
        $shining->mask($this->createMask($firefly), false);
        $img->insert($shining, "top-left");
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

    private function createMask(FireflyState $firefly): Image
    {
        $img = $this->imageManager->canvas($this->fieldSize, $this->fieldSize);
        $img->fill('#000');
        $radius = $firefly->getShine() * $this->shineRadius;
        $intensity = 0;
        while ($radius > 0 and $intensity <= 1) {
            $component = dechex((int)(hexdec("ff")*$intensity));
            if (strlen($component) === 1) {
                $component = "0" . $component;
            }
            $img->circle(
                (int) $radius,
                $firefly->getX(),
                $firefly->getY(),
                function ($draw) use ($component) {
                    $draw->background(sprintf('#%s%s%s', $component, $component, $component));
                }
            );
            $radius = $radius - ($this->shineStep * $this->shineRadius);
            $intensity = $intensity + $this->shineStep;
        }
        return $img;
    }
}
