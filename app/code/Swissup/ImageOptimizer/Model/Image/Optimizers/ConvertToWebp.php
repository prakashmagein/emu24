<?php

namespace Swissup\ImageOptimizer\Model\Image\Optimizers;

use Spatie\ImageOptimizer\Image;

// @phpstan-ignore-next-line
class ConvertToWebp extends \Spatie\ImageOptimizer\Optimizers\BaseOptimizer
{
    public $binaryName = 'cwebp';

    /**
     *
     * @param  Image  $image
     * @return boolean
     */
    public function canHandle(Image $image): bool
    {
        return in_array($image->mime(), [
//            'image/webp',
            'image/png',
            'image/jpeg',
        ]);
    }

    /**
     *
     * @return string
     */
    public function getCommand(): string
    {
        $optionString = implode(' ', $this->options);/** @phpstan-ignore-line */
        $imagePath = $this->imagePath;/** @phpstan-ignore-line */
        $binaryPath = $this->binaryPath;/** @phpstan-ignore-line */
        $binaryName = $this->binaryName;/** @phpstan-ignore-line */

        $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
        // $outputFile = preg_replace('/' . $extension . '$/', 'webp', $this->imagePath); // old before #18
        $outputFile = preg_replace('/' . $extension . '$/', $extension . '.webp', $imagePath);

        return "\"{$binaryPath}{$binaryName}\" {$optionString}"
            . ' ' . escapeshellarg($imagePath)
            . ' -o ' . escapeshellarg($outputFile);
    }
}
