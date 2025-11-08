<?php
namespace Swissup\ImageOptimizer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Config extends AbstractHelper
{

    const CONFIG_XML_PATH_IMAGE_OPTIMISE_ENABLE           = 'pagespeed/image/optimize_enable';
    const CONFIG_XML_PATH_IMAGE_OPTIMISE_WEBP_ENABLE      = 'pagespeed/image/optimize_webp_enable';

    const CONFIG_XML_PATH_IMAGE_OPTIMISE_PROVIDER         = 'pagespeed/image/provider';
    const CONFIG_XML_PATH_IMAGE_OPTIMISE_PROVIDER_APIURL  = 'pagespeed/image/provider_apiurl';
    const CONFIG_XML_PATH_IMAGE_OPTIMISE_PROVIDER_APIKEY  = 'pagespeed/image/provider_apikey';
    const CONFIG_XML_PATH_IMAGE_OPTIMISE_CRON_ENABLE      = 'pagespeed/image/optimize_cron_enable';
    const CONFIG_XML_PATH_IMAGE_OPTIMISE_CRON_LIMIT       = 'pagespeed/image/optimize_cron_limit';
    const CONFIG_XML_PATH_IMAGE_OPTIMIZE_TIMEOUT          = 'pagespeed/image/optimize_timeout';
    const CONFIG_XML_PATH_IMAGE_OPTIMIZE_LOGGING          = 'pagespeed/image/optimize_logging';
    const CONFIG_XML_PATH_CATALOG_MEDIA_URL_FORMAT        = 'web/url/catalog_media_url_format';

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaDirectoryWriter;

    /**
     * @param Context $context
     * @param Filesystem $filesystem
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->mediaDirectoryWriter = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     *
     * @param  string $key
     * @return mixed
     */
    private function getConfig($key)
    {
        return $this->scopeConfig->getValue($key, ScopeInterface::SCOPE_STORE);
    }

    /**
     *
     * @param  string $key
     * @return boolean
     */
    private function isSetFlag($key)
    {
        return $this->scopeConfig->isSetFlag($key, ScopeInterface::SCOPE_STORE);
    }

    /**
     *
     * @return boolean
     */
    public function isEnable()
    {
        return true;
    }

    /**
     *
     * @return boolean
     */
    public function isImageOptimizerEnable()
    {
        return $this->isEnable() && $this->isSetFlag(self::CONFIG_XML_PATH_IMAGE_OPTIMISE_ENABLE);
    }

    /**
     *
     * @return boolean
     */
    public function isWebPEnable()
    {
        return $this->isImageOptimizerEnable()
            && $this->isSetFlag(self::CONFIG_XML_PATH_IMAGE_OPTIMISE_WEBP_ENABLE);
    }

    public function getResizeCommandTargetDirs()
    {
        return [
            'wysiwyg',
            'catalog/category',
            'easybanner',
            'easyslide',
            'swissup',
            'highlight',
            'easycatalogimg',
            'prolabels',
            'testimonials',
            'mageplaza',
            'lightboxpro',
            'logo',
            'attribute',
            '.renditions',
            '.renditions/wysiwyg',
            'reviews/customer'
        ];
    }

    /**
     *
     * @return int
     */
    public function getImageOptimizerProvider()
    {
        return (int) $this->getConfig(self::CONFIG_XML_PATH_IMAGE_OPTIMISE_PROVIDER);
    }

    /**
     *
     * @return boolean
     */
    public function isImageOptimizersRemote()
    {
        return $this->isImageOptimizerEnable() &&
            $this->getImageOptimizerProvider() === \Swissup\ImageOptimizer\Model\Config\Source\Image\Optimize\Provider::REMOTE;
    }

    /**
     *
     * @return boolean
     */
    public function isImageOptimizationBasedOnQueryParams()
    {
        return $this->getConfig(self::CONFIG_XML_PATH_CATALOG_MEDIA_URL_FORMAT)
            === \Magento\Catalog\Model\Config\CatalogMediaConfig::IMAGE_OPTIMIZATION_PARAMETERS;
    }

    /**
     * Retrieve Base URL
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_urlBuilder->getBaseUrl();
    }
    /**
     * Retrieve Media Dir
     *
     * @return string
     */
    public function getMediaDir()
    {
        return $this->mediaDirectoryWriter->getAbsolutePath();
    }

    /**
     * @return string
     */
    public function getImageOptimizeServiceAPIUrl()
    {
        return $this->getConfig(self::CONFIG_XML_PATH_IMAGE_OPTIMISE_PROVIDER_APIURL);
    }
    /**
     * @return string
     */
    public function getImageOptimizeServiceAPIKey()
    {
        return $this->getConfig(self::CONFIG_XML_PATH_IMAGE_OPTIMISE_PROVIDER_APIKEY);
    }

    /**
     *
     * @return int
     */
    public function getImageOptimizerTimeout()
    {
        return (int) $this->getConfig(self::CONFIG_XML_PATH_IMAGE_OPTIMIZE_TIMEOUT);
    }

    /**
     * @return bool
     */
    public function useLoggingUntilImageOptimise()
    {
        return $this->isImageOptimizerEnable()
            && $this->isSetFlag(self::CONFIG_XML_PATH_IMAGE_OPTIMIZE_LOGGING)
        ;
    }

    /**
     * @return boolean
     */
    public function isCronEnabled()
    {
        return $this->isEnable()
            && $this->isImageOptimizerEnable()
            && $this->isSetFlag(self::CONFIG_XML_PATH_IMAGE_OPTIMISE_CRON_ENABLE);
    }

    /**
     * @return int
     */
    public function getCronLimit()
    {
        return (int) $this->getConfig(self::CONFIG_XML_PATH_IMAGE_OPTIMISE_CRON_LIMIT);
    }
}
