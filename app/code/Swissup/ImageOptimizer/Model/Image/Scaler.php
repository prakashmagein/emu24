<?php
declare(strict_types=1);

namespace Swissup\ImageOptimizer\Model\Image;

use Magento\Framework\Filesystem\Io\File as IoFile;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Scaler
{
    /**
     * @var \Swissup\ImageOptimizer\Model\Image\Resizer
     */
    private $imageResizer;

    /**
     * @var IoFile
     */
    private $ioFile;

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

    /**
     * @var array
     */
    private $resolutions = [];

    public function __construct(
        \Swissup\ImageOptimizer\Model\Image\Resizer $imageResizer,
        IoFile $ioFile
    ) {
        $this->imageResizer = $imageResizer;
        $this->ioFile = $ioFile;
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
     * @param array $resolutions
     * @return $this
     */
    public function setResolutions($resolutions)
    {
        $this->resolutions = $resolutions;
        return $this;
    }

    /**
     *
     * @return void
     */
    public function execute()
    {
        $params = $this->params;

        $width = $params['image_width'];
        $height = $params['image_height'];
        $basename = $this->getBasename();

        foreach ($this->resolutions as $resolution) {
            $params['image_width'] = (int) ceil($width * $resolution);
            $params['image_height'] = (int) ceil($height * $resolution);

            $destination = str_replace(
                $basename,
                $resolution . 'x' . DIRECTORY_SEPARATOR . $basename,
                $this->destination
            );

            $this->imageResizer
                ->setOrigin($this->origin)
                ->setParams($params)
                ->setDestination($destination)
                ->execute()
            ;
        }
    }

    /**
     * @return mixed
     */
    private function getBasename()
    {
        $pathInfo = $this->ioFile->getPathInfo($this->destination);
        return $pathInfo['basename'];
    }
}
