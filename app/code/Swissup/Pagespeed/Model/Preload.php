<?php
namespace Swissup\Pagespeed\Model;

use Swissup\Pagespeed\Helper\Config;
use Magento\Framework\App\Response\Http as ResponseHttp;

class Preload
{
    /**
     * @var array
     */
    private $assets = [];

    /**
     * @param array|string $links
     * @param string $as
     * @param string $rel
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function add($links, $as = 'style', $rel = 'preload'/*, $type = ''*/)
    {
        $this->assets = array_merge_recursive($this->assets, [$rel => [$as => $links]]);
        return $this;
    }

    /**
     * @return array
     */
    public function getAssets()
    {
        return $this->assets;
    }
}
