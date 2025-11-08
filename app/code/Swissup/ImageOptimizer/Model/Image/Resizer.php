<?php
declare(strict_types=1);

namespace Swissup\ImageOptimizer\Model\Image;

use Magento\Framework\Image;
use Magento\Framework\Image\Factory as ImageFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Resizer
{
    /**
     * @var ImageFactory
     */
    private $imageFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $origin;

    /**
     * @var string
     */
    private $destination;

    /**
     * @var array
     */
    private $params = [];

    public function __construct(ImageFactory $imageFactory, \Psr\Log\LoggerInterface $logger)
    {
        $this->imageFactory = $imageFactory;
        $this->logger = $logger;
    }

    /**
     * @param $path
     * @return $this
     */
    public function setOrigin($path)
    {
        $this->origin = $path;
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
     * @param $path
     * @return $this
     */
    public function setDestination($path)
    {
        $this->destination = $path;
        return $this;
    }

    /**
     * @return void
     */
    public function execute()
    {
        try {
            $params = $this->params;
            $image = $this->makeImage($this->origin, $params);

            if (!empty($params['image_width']) && !empty($params['image_height'])) {
                $image->resize($params['image_width'], $params['image_height']);
            }

            $image->save($this->destination);
            unset($image);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            //throw $e;
        }
    }

    /**
     * Make image
     * @param string $origin
     * @param array $params
     * @throws \Exception
     * @return Image
     */
    private function makeImage(string $origin, array $params): Image
    {
        $image = $this->imageFactory->create($origin);
        if (isset($params['keep_aspect_ratio'])) {
            $image->keepAspectRatio($params['keep_aspect_ratio']);
        }
        if (isset($params['keep_frame'])) {
            $image->keepFrame($params['keep_frame']);
        }
        if (isset($params['keep_transparency'])) {
            $image->keepTransparency($params['keep_transparency']);
        }
        if (isset($params['constrain_only'])) {
            $image->constrainOnly($params['constrain_only']);
        }
        if (isset($params['background'])) {
            $image->backgroundColor($params['background']);
        }
        if (isset($params['quality'])) {
            $image->quality($params['quality']);
        }
        return $image;
    }
}
