<?php
declare(strict_types=1);

namespace Swissup\ImageOptimizer\Model;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Filesystem\Io\File as IoFile;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImageResize
{
    /**
     * @var \Swissup\ImageOptimizer\Model\Image\Generator\ProductImageGenerator
     */
    private $productImageGenerator;

    /**
     * @var \Swissup\ImageOptimizer\Model\Image\Generator\CustomImageGenerator
     */
    private $customImageGenarator;

    /**
     * @var \Swissup\ImageOptimizer\Model\Image\Resizer
     */
    private $imageResizer;

    /**
     * @var \Swissup\ImageOptimizer\Model\Image\Scaler
     */
    private $imageScaler;

    /**
     * @var \Magento\Catalog\Model\Product\Image\ParamsBuilder
     */
    private $paramsBuilder;

    /**
     * @var \Swissup\ImageOptimizer\Model\View\GetCatalogMediaEntities
     */
    private $getCatalogMediaEntities;

    /**
     * @var \Swissup\ImageOptimizer\Model\View\Asset\ImageDestanationPathResolver
     */
    private $destinationPathResolver;

    /**
     * @var \Swissup\Image\Helper\Dimensions
     */
    private $imageDimension;

    /**
     * @var IoFile
     */
    private $ioFile;

    /**
     *
     * @var string
     */
    private $filenameFilter;

    /**
     *
     * @var integer
     */
    private $limit = 100000;

    /**
     * @param \Swissup\ImageOptimizer\Model\Image\Generator\ProductImageGenerator $productImageGenerator
     * @param \Swissup\ImageOptimizer\Model\Image\Generator\CustomImageGenerator $customImageGenarator
     * @param \Swissup\ImageOptimizer\Model\Image\Resizer $imageResizer
     * @param \Swissup\ImageOptimizer\Model\Image\Scaler $imageScaler
     * @param \Magento\Catalog\Model\Product\Image\ParamsBuilder $paramsBuilder
     * @param \Swissup\ImageOptimizer\Model\View\GetCatalogMediaEntities $getCatalogMediaEntities
     * @param \Swissup\ImageOptimizer\Model\View\Asset\ImageDestanationPathResolver $destinationPathResolver
     * @param \Swissup\Image\Helper\Dimensions $imageDimension
     * @param IoFile $ioFile
     * @internal param ProductImage $gallery
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Swissup\ImageOptimizer\Model\Image\Generator\ProductImageGenerator $productImageGenerator,
        \Swissup\ImageOptimizer\Model\Image\Generator\CustomImageGenerator $customImageGenarator,
        \Swissup\ImageOptimizer\Model\Image\Resizer $imageResizer,
        \Swissup\ImageOptimizer\Model\Image\Scaler $imageScaler,
        \Magento\Catalog\Model\Product\Image\ParamsBuilder $paramsBuilder,
        \Swissup\ImageOptimizer\Model\View\GetCatalogMediaEntities $getCatalogMediaEntities,
        \Swissup\ImageOptimizer\Model\View\Asset\ImageDestanationPathResolver $destinationPathResolver,
        \Swissup\Image\Helper\Dimensions $imageDimension,
        IoFile $ioFile
    ) {
        $this->productImageGenerator = $productImageGenerator;
        $this->customImageGenarator = $customImageGenarator;
        $this->imageResizer = $imageResizer;
        $this->imageScaler = $imageScaler;
        $this->paramsBuilder = $paramsBuilder;
        $this->getCatalogMediaEntities = $getCatalogMediaEntities;
        $this->destinationPathResolver = $destinationPathResolver;
        $this->imageDimension = $imageDimension;
        $this->ioFile = $ioFile;
    }

    /**
     * Create resized images of different sizes from themes
     * @param array|null $themes
     * @return \Generator
     * @throws NotFoundException
     */
    public function resizeAllProductImages(?array $themes = null): \Generator
    {
        if (!empty($this->filenameFilter)) {
            $this->productImageGenerator->setFilenameFilter($this->filenameFilter);
        }

        $count = $this->productImageGenerator->getCountAllProductImages();
        if (!$count) {
            yield 'None' => 0;
            // throw new NotFoundException(__('Cannot resize images - product images not found'));
        }

        $productImageGenerator = $this->productImageGenerator
            ->setPageSize($this->limit)
            ->create();
        $images = [];
        foreach ($productImageGenerator as $image) {
            $images[] = $image;
        }
        $count = count($images);
        if (!$count) {
            yield 'None' => 0;
            // throw new NotFoundException(__('Cannot resize images - product images not found'));
        }

        $mediaEntities = $this->getCatalogMediaEntities->get($themes ?? []);
        foreach ($images as $image) {
            $originalImageName = $image['filename'];
            $originalImagePath = $image['path'];

            foreach ($mediaEntities as $mediaEntity) {
                $imageParams = $this->paramsBuilder->build($mediaEntity);
                $destinationImagePath = $this->destinationPathResolver
                    ->setName($originalImageName)
                    ->setParams($imageParams)
                    ->getPath();

                $this->imageResizer
                    ->setOrigin($originalImagePath)
                    ->setParams($imageParams)
                    ->setDestination($destinationImagePath)
                    ->execute();

                if (empty($imageParams['image_width']) || empty($imageParams['image_height'])) {
                    $dimensions = $this->imageDimension->getDimensions($destinationImagePath);
                    if (!empty($dimensions['width']) && !empty($dimensions['height'])) {
                        $imageParams['image_width'] = $dimensions['width'];
                        $imageParams['image_height'] = $dimensions['height'];
                    }
                }

                if (!empty($imageParams['image_width']) && !empty($imageParams['image_height'])) {
                    $resolutions = [0.5, 0.75,/*1,*/ 2, 3];
                    $this->imageScaler->setOrigin($originalImagePath)
                        ->setParams($imageParams)
                        ->setDestination($destinationImagePath)
                        ->setResolutions($resolutions)
                        ->execute()
                    ;
                }
            }

            yield $originalImageName => $count;
        }
    }

    /**
     *
     * @return \Generator
     * @throws NotFoundException
     */
    public function resizeCustomImages()
    {
        $mediaEntity = [
            'type' => "image",
            'id' => "swissup_pagespeed_wysiwyg_default"
        ];
        $imageParams = $this->paramsBuilder->build($mediaEntity);

        $customImageGenerator = $this->customImageGenarator
            ->setFilenameFilter($this->filenameFilter)
            ->setDisallowedFilesFilter('\.original\.')
            ->setPageSize($this->limit)
            ->create();

        $images = [];
        foreach ($customImageGenerator as $image) {
            $images[] = $image;
        }
        $count = count($images);
        if (!$count) {
            yield 'None' => 0;
        }

        foreach ($images as $image) {
            $imageFilename = $image['filename'];
            $imagePath = $image['path'];
            $dimensions = $this->imageDimension->getDimensions($imagePath);
            if (!empty($dimensions['width']) && !empty($dimensions['height'])) {
                $imageParams = [
                    'image_width' => $dimensions['width'],
                    'image_height' => $dimensions['height']
                ];

                $imagePathInfo = $this->ioFile->getPathInfo($imagePath);
                $originalImagePath = $imagePathInfo['dirname'] . DIRECTORY_SEPARATOR
                    . $imagePathInfo['filename']
                    . '.original.'
                    . $imagePathInfo['extension']
                ;
                if ($imagePathInfo['extension'] === 'png') {
                    $imageParams['keep_transparency'] = true;
                }
                if (!$this->ioFile->fileExists($originalImagePath)
//                    && $this->ioFile->isWriteable($originalImagePath)
//                    && $this->ioFile->isWriteable($imagePath)
                ) {
//                    $this->ioFile->cp($imagePath, $originalImagePath);
                    copy($imagePath, $originalImagePath);
                    $destinationImagePath = $imagePath;
                    $this->imageResizer
                        ->setOrigin($originalImagePath)
                        ->setParams($imageParams)
                        ->setDestination($destinationImagePath)
                        ->execute();

                    $resolutions = [0.5, 0.75];
                    $this->imageScaler
                        ->setOrigin($originalImagePath)
                        ->setParams($imageParams)
                        ->setDestination($destinationImagePath)
                        ->setResolutions($resolutions)
                        ->execute()
                    ;
                }
            }
            yield $imageFilename => $count;
        }
    }

    /**
     *
     * @param string $filename
     */
    public function setFilenameFilter($filename)
    {
        $this->filenameFilter = (string) $filename;
        return $this;
    }

    /**
     *
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = (int) $limit;
        return $this;
    }
}
