<?php

declare(strict_types=1);

namespace Newla\Image;

use GdImage;

class ImageProcessor
{
    protected ?GdImage $image = null;
    protected string $sourcePath;
    protected string $mimeType;
    protected int $width;
    protected int $height;

    public function __construct(string $path)
    {
        $this->sourcePath = $path;
        $this->load($path);
    }

    protected function load(string $path): void
    {
        if (!file_exists($path)) {
            throw new ImageException("Image file not found: [{$path}]");
        }

        $info = @getimagesize($path);
        if ($info === false) {
            throw new ImageException("Invalid image file: [{$path}]");
        }

        $this->width = $info[0];
        $this->height = $info[1];
        $this->mimeType = $info['mime'];

        $this->image = match ($this->mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            'image/gif' => @imagecreatefromgif($path),
            'image/avif' => function_exists('imagecreatefromavif') ? @imagecreatefromavif($path) : false,
            default => false,
        };

        if ($this->image === false || $this->image === null) {
            throw new ImageException("Unsupported image format or corrupt file: [{$this->mimeType}]");
        }

        // Enable alpha blending and preserve transparency
        imagealphablending($this->image, true);
        imagesavealpha($this->image, true);
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function resize(int $targetWidth, int $targetHeight, bool $keepRatio = true): static
    {
        if ($keepRatio) {
            $ratio = min($targetWidth / $this->width, $targetHeight / $this->height);
            $newWidth = (int) round($this->width * $ratio);
            $newHeight = (int) round($this->height * $ratio);
        } else {
            $newWidth = $targetWidth;
            $newHeight = $targetHeight;
        }

        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        $this->preserveTransparency($newImage);

        imagecopyresampled($newImage, $this->image, 0, 0, 0, 0, $newWidth, $newHeight, $this->width, $this->height);

        $this->image = $newImage;
        $this->width = $newWidth;
        $this->height = $newHeight;

        return $this;
    }

    public function thumbnail(int $width, int $height): static
    {
        $srcRatio = $this->width / $this->height;
        $targetRatio = $width / $height;

        if ($srcRatio > $targetRatio) {
            // Source is wider -> crop sides
            $cropHeight = $this->height;
            $cropWidth = (int) round($this->height * $targetRatio);
            $cropX = (int) round(($this->width - $cropWidth) / 2);
            $cropY = 0;
        } else {
            // Source is taller -> crop top/bottom
            $cropWidth = $this->width;
            $cropHeight = (int) round($this->width / $targetRatio);
            $cropX = 0;
            $cropY = (int) round(($this->height - $cropHeight) / 2);
        }

        $newImage = imagecreatetruecolor($width, $height);
        $this->preserveTransparency($newImage);

        imagecopyresampled($newImage, $this->image, 0, 0, $cropX, $cropY, $width, $height, $cropWidth, $cropHeight);

        $this->image = $newImage;
        $this->width = $width;
        $this->height = $height;

        return $this;
    }

    public function crop(int $x, int $y, int $width, int $height): static
    {
        $newImage = imagecreatetruecolor($width, $height);
        $this->preserveTransparency($newImage);

        imagecopyresampled($newImage, $this->image, 0, 0, $x, $y, $width, $height, $width, $height);

        $this->image = $newImage;
        $this->image = $newImage;
        $this->width = $width;
        $this->height = $height;

        return $this;
    }

    public function rotate(float $angle, int $bgColor = 0): static
    {
        $rotated = imagerotate($this->image, $angle, $bgColor);
        if ($rotated !== false) {
            imagedestroy($this->image);
            $this->image = $rotated;
            $this->width = imagesx($rotated);
            $this->height = imagesy($rotated);
        }
        return $this;
    }

    public function optimize(int $quality = 85): static
    {
        // Re-encoding already strips malicious EXIF and compresses
        return $this;
    }

    public function save(string $outputPath, ?string $format = null, int $quality = 85): bool
    {
        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $format = $format ?? pathinfo($outputPath, PATHINFO_EXTENSION);
        $format = strtolower($format);

        return match ($format) {
            'jpg', 'jpeg' => imagejpeg($this->image, $outputPath, $quality),
            'png' => imagepng($this->image, $outputPath, (int) round((100 - $quality) / 10)),
            'webp' => imagewebp($this->image, $outputPath, $quality),
            'gif' => imagegif($this->image, $outputPath),
            'avif' => function_exists('imageavif') ? imageavif($this->image, $outputPath, $quality) : imagewebp($this->image, $outputPath, $quality),
            default => imagejpeg($this->image, $outputPath, $quality),
        };
    }

    public function toWebp(string $outputPath, int $quality = 85): bool
    {
        return $this->save($outputPath, 'webp', $quality);
    }

    protected function preserveTransparency(GdImage $target): void
    {
        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 255, 255, 255, 127);
        imagefilledrectangle($target, 0, 0, imagesx($target), imagesy($target), $transparent);
    }
}