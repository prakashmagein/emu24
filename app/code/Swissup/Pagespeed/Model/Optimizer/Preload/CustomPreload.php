<?php
namespace Swissup\Pagespeed\Model\Optimizer\Preload;

use Swissup\Pagespeed\Helper\Config;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Swissup\Pagespeed\Model\Optimizer\AbstractOptimizer;

class CustomPreload extends AbstractOptimizer
{
    /**
     *
     * @var \Swissup\Pagespeed\Model\Preload
     */
    private $preloader;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $ioFile;

    /**
     * @var \Swissup\Pagespeed\Model\View\Asset\PlaceholderReplacer
     */
    private $placeholderReplacer;

    /**
     * @param Config $config
     * @param \Swissup\Pagespeed\Model\Preload $preloader
     * @param \Magento\Framework\Filesystem\Io\File $ioFile
     */
    public function __construct(
        Config $config,
        \Swissup\Pagespeed\Model\Preload $preloader,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Swissup\Pagespeed\Model\View\Asset\PlaceholderReplacer $placeholderReplacer
    ) {
        parent::__construct($config);
        $this->preloader = $preloader;
        $this->ioFile = $ioFile;
        $this->placeholderReplacer = $placeholderReplacer;
    }

    /**
     * Perform result postprocessing
     *
     * @param ResponseHttp $response
     * @return ResponseHttp
     */
    public function process(?ResponseHttp $response = null)
    {
        if (!$response || !$this->config->isLinkPreloadEnabled()) {
            return $response;
        }

        $links = $this->config->getCustomLinkForPreload();
        $linksByTypes = [];
        foreach ($links as $link) {

            $link = $this->placeholderReplacer->process($link);

            if (!filter_var($link, FILTER_VALIDATE_URL)) {
                continue;
            }

            $extension = $this->getFileExtension($link);
            switch ($extension) {
                case 'js':
                    $linksByTypes['script'][] = ['as' => 'js', 'href' => $link];
                    break;
                case 'css':
                    $linksByTypes['style'][] = ['as' => 'style', 'href' => $link];
                    break;
                case 'eot':
                case 'otf':
                case 'ttf':
                case 'woff':
                case 'woff2':
                    $linksByTypes['font'][] = ['as' => 'font', 'href' => $link];
                    break;
                case 'ico':
                case 'webp':
                case 'jpg':
                case 'jpeg':
                case 'gif':
                case 'bmp':
                case 'svg':
                case 'png':
                    $linksByTypes['image'][] = ['as' => 'image', 'href' => $link];
                    break;
            }
        }

        foreach ($linksByTypes as $as => $assets) {
            $this->preloader->add($assets, $as, 'preload');
        }

        return $response;
    }

    /** instead pathinfo($path, PATHINFO_EXTENSION);
     * @param $path
     * @return string
     */
    private function getFileExtension($path)
    {
        $pathInfo = $this->ioFile->getPathInfo($path);
        return isset($pathInfo['extension']) ? $pathInfo['extension'] : false;
    }
}
