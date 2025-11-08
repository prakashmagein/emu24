<?php
declare(strict_types=1);

namespace Swissup\ImageOptimizer\Model\View\Asset;

use Magento\Catalog\Model\View\Asset\ImageFactory as AssertImageFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImageDestanationPathResolver
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $params = [];

    /**
     * @var bool|null
     */
    private $isOldMagento;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $magentoMetadata;

    /**
     * @var \Magento\Catalog\Model\View\Asset\ImageFactory
     */
    private $assertImageFactory;

    /**
     *
     * @param \Magento\Catalog\Model\View\Asset\ImageFactory $assertImageFactory
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Catalog\Model\View\Asset\ImageFactory $assertImageFactory,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->assertImageFactory = $assertImageFactory;
        $this->magentoMetadata = $productMetadata;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param $params
     * @return $this
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @return mixed
     */
    private function getImageAsset()
    {
        $originalImageName = $this->name;
        $imageParams = $this->params;
        $miscParams = $this->convertToPatchedFormat($imageParams);
        return $this->assertImageFactory->create([
            'miscParams' => $miscParams,
            'filePath' => $originalImageName
        ]);
    }

    /**
     *
     * @return string
     */
    public function getPath()
    {
        $imageAsset = $this->getImageAsset();
        return  $imageAsset->getPath();
    }

    /**
     * @param $miscParams
     * @return array
     */
    private function convertToPatchedFormat($miscParams)
    {
        $return = [];

        $isOld = $this->isOldMagento();

        if (array_key_exists('background', $miscParams)) {
            $background = $miscParams['background'];
            if (is_array($background) && $isOld) {
                $background = 'rgb' . implode(',', $background);
            }
            $miscParams['background'] = $background;
        }

        $keys = [
            'image_height',
            'image_width',
        ];

        $overwrittenKeys = [
            'background',
            'angle',
            'quality',
            'keep_aspect_ratio',
            'keep_frame',
            'keep_transparency',
            'constrain_only',
        ];
        $keys =  array_merge($keys, $overwrittenKeys);

        if ($isOld) {
            $keys = [
                'image_height',
                'image_width',
                'quality',
                'angle',
                'keep_aspect_ratio',
                'keep_frame',
                'keep_transparency',
                'constrain_only',
                'background'
            ];
        }

        $watermarkKeys = [
            'watermark_file',
            'watermark_image_opacity',
            'watermark_position',
            'watermark_width',
            'watermark_height'
        ];
        $keys =  array_merge($keys, $watermarkKeys);

        foreach ($keys as $key) {
            if (array_key_exists($key, $miscParams)) {
                $return[$key] = $miscParams[$key];
            }
        }

        // ksort($return);

        return $return;
    }

    /**
     * @return bool|int
     */
    private function isOldMagento()
    {
        if ($this->isOldMagento === null) {
            $this->isOldMagento = version_compare($this->magentoMetadata->getVersion(), '2.3.0', '<');
        }

        return $this->isOldMagento;
    }
}
