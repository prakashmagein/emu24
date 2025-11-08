<?php
namespace Swissup\Pagespeed\Model\Image\WebP;

use Swissup\Pagespeed\Model\Image\File;

class UrlResolver
{
    /**
     * @var File
     */
    private File $file;

    /**
     * @var string[]
     */
    private array $allowedExtensions = ['jpg', 'jpeg', 'png'];

    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * Return corresponding WebP version of the image, if exists.
     *
     * @param string $originalUrl
     * @return string|null
     */
    public function resolve(string $originalUrl): ?string
    {
        $basename = $this->file->getFileBasename($originalUrl);
        $extension = strtolower($this->file->getFileExtension($basename));

        if (!in_array($extension, $this->allowedExtensions, true)) {
            return null;
        }

        $filename = $this->file->getFilename($basename);
        $candidateNames = [
            "{$filename}.{$extension}.webp",
            "{$filename}.webp"
        ];

        foreach ($candidateNames as $webpBasename) {
            $webpUrl = str_replace("/{$basename}", "/{$webpBasename}", $originalUrl);

            if (
                ($this->file->isMediaProductUrl($webpUrl) && $this->file->isMediaImageFileExist($webpUrl)) ||
                ($this->file->isMediaCustomUrl($webpUrl) && $this->file->isMediaImageFileExist($webpUrl)) ||
                ($this->file->isPubStaticUrl($webpUrl) && $this->file->isPubStaticImageFileExist($webpUrl))
            ) {
                return $webpUrl;
            }
        }

        return null;
    }

    /**
     * Check if image extension is supported for WebP replacement
     *
     * @param string $extension
     * @return bool
     */
    public function isSupported(string $extension): bool
    {
        return in_array(strtolower($extension), $this->allowedExtensions, true);
    }
}
