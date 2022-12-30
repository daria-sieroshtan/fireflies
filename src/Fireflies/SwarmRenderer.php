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
    const SHINING = "#ff0";
    const DEFAULT_SHINE_RADIUS = 50;
    const DEFAULT_SHINE_STEP = 0.05;
    const EPSILON = 10e-4;

    private string $rootDir;
    private int $fieldSize;
    private int $framesDumped;
    private ImageManager $imageManager;
    private int $shineRadius;
    private float $shineStep;
    private array $fireflyTemplates;

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
        $this->fireflyTemplates = $this->prepareFireflyTemplates();
    }

    /**
     * @param FireflyState[] $fireflies
     */
    public function renderSwarm(array $fireflies): void
    {
        $img = $this->getBaseImage();
        $this->addFireflies($img, $fireflies);
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

    /**
     * @param FireflyState[] $fireflies
     */
    private function addFireflies(Image $img, array $fireflies): void
    {
        foreach ($fireflies as $fireflyState) {
            $this->addFirefly($img, $fireflyState);
        }
    }

    private function addFirefly(Image $img, FireflyState $fireflyState): void
    {
        if ($fireflyState->getShine() === 0) {
            return;
        }
        $approximateShine = round($fireflyState->getShine() / self::DEFAULT_SHINE_STEP) * self::DEFAULT_SHINE_STEP;
        if ($approximateShine == 0.0) {
            return;
        }
        $firefly = $this->fireflyTemplates[strval($approximateShine)];
        $offset = (int) ($this->shineRadius / 2);
        $img->insert(
            $firefly,
            "top-left",
            $fireflyState->x - $offset,
            $fireflyState->y - $offset
        );
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

    private function prepareFireflyTemplates(): array
    {
        $templates = [];
        $intensity = self::DEFAULT_SHINE_STEP;
        while ($intensity <= 1 + self::EPSILON) {
            $templates[strval($intensity)] = $this->prepareFireflyTemplate($intensity);
            $intensity += self::DEFAULT_SHINE_STEP;
        }
        return $templates;
    }

    private function prepareFireflyTemplate(float $maxIntensity): Image
    {
        $maxRadius = (int) ($this->shineRadius * $maxIntensity);
        $mask = $this->imageManager->canvas($maxRadius*2, $maxRadius*2);
        $mask->fill('#000');
        $intensity = 0.;
        $radius = $maxRadius;
        while ($radius > 0 and $intensity <= $maxIntensity) {
            $component = dechex((int)(hexdec("ff")*$intensity));
            if (strlen($component) === 1) {
                $component = "0" . $component;
            }
            $mask->circle(
                (int) $radius,
                (int) ($maxRadius / 2),
                (int) ($maxRadius / 2),
                function ($draw) use ($component) {
                    $draw->background(sprintf('#%s%s%s', $component, $component, $component));
                }
            );
            $intensity = $intensity + $this->shineStep;
            $radius = $radius - ($this->shineStep * $this->shineRadius);
        }

        $shining = $this->imageManager->canvas($this->shineRadius*2, $this->shineRadius*2);
        $shining->fill(self::SHINING);
        $shining->mask($mask, false);
        return $shining;
    }
}
