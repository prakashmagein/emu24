<?php
namespace Swissup\Askit\Helper;

use Magento\Framework\UrlInterface;
use Swissup\Askit\Api\Data\MessageInterface;

class Url extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ENTITY_PRODUCT_URL_PATH_EDIT  = 'catalog/product/edit';
    const ENTITY_CATEGORY_URL_PATH_EDIT = 'catalog/category/edit';
    const ENTITY_PAGE_URL_PATH_EDIT     = 'cms/page/edit';

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var \Magento\Cms\Model\PageRepository
     */
    private $pageRepository;

    /**
     * Cms page
     *
     * @var \Magento\Cms\Helper\Page
     */
    private $cmsPageHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param  \Magento\Catalog\Model\ProductRepository $productRepository
     * @param  \Magento\Catalog\Model\CategoryRepository $categoryRepository
     * @param  \Magento\Cms\Model\PageRepository $pageRepository
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Cms\Model\PageRepository $pageRepository,
        \Magento\Cms\Helper\Page $cmsPage
    ) {

        $this->productRepository = $productRepository;
        $this->pageRepository = $pageRepository;
        $this->categoryRepository = $categoryRepository;

        $this->cmsPageHelper = $cmsPage;

        parent::__construct($context);
    }

  /**
   * @param \Magento\Framework\UrlInterface $urlBuilder
   */
    public function setUrlBuilder(\Magento\Framework\UrlInterface $urlBuilder)
    {
        $this->_urlBuilder = $urlBuilder;
        return $this;
    }

    public function getQuestionUrl($questionId)
    {
        $prefix = \Swissup\Askit\Controller\Router::ROUTE_PREFIX;
        return $this->_urlBuilder->getUrl(null, [
            '_direct' => $prefix . '?questionId=' . $questionId
        ]);
    }

    /**
     * @param $type
     * @param $id
     * @param $edit
     * @return array
     */
    public function get($type, $id, $edit = true)
    {
        $label = $href = false;
        try {
            switch ($type) {
                case MessageInterface::TYPE_CMS_PAGE:
                    $page = $this->pageRepository->getById($id);
                    $label = $page->getTitle();

                    $href = $edit ? $this->_urlBuilder->getUrl(
                        self::ENTITY_PAGE_URL_PATH_EDIT,
                        ['page_id' => $id]
                    ) : $this->cmsPageHelper->getPageUrl($page->getId());
                    break;
                case MessageInterface::TYPE_CATALOG_CATEGORY:
                    /** @var \Magento\Catalog\Model\Category $category */
                    $category = $this->categoryRepository->get($id);
                    $label = $category->getName();
                    $href = $edit ? $this->_urlBuilder->getUrl(
                        self::ENTITY_CATEGORY_URL_PATH_EDIT,
                        ['id' => $id]
                    ) : $category->getUrl();
                    break;
                case MessageInterface::TYPE_CATALOG_PRODUCT:
                default:
                    /** @var \Magento\Catalog\Model\Product $product */
                    $product = $this->productRepository->getById($id);
                    $label = $product->getName();
                    $href = $edit ? $this->_urlBuilder->getUrl(
                        self::ENTITY_PRODUCT_URL_PATH_EDIT,
                        ['id' => $id]
                    ) : $product->getUrlModel()->getUrl($product);
                    break;
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $label = __('Entity was deleted');
            $href = '#';
        }

        return ['label' => $label, 'href' => $href];
    }

    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl();
    }

    /**
     * Get either a Gravatar URL or complete image tag for a specified email address.
     *
     * @param string $email The email address
     * @param string|integer $s Size in pixels, defaults to 80px [ 1 - 2048 ]
     * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
     * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
     * @param bool $img True to return a complete IMG tag False for just the URL
     * @param array $atts Optional, additional key/value attributes to include in the IMG tag
     * @return String containing either just a URL or a complete image tag
     * @source http://gravatar.com/site/implement/images/php/
     */
    public function getGravatar($email, $s = 80, $d = 'mm', $r = 'g', $img = true, $atts = [])
    {
        $url = '//www.gravatar.com/avatar/';
        $url .= hash('md5', strtolower(trim($email)));
        $url .= "?s=$s&d=$d&r=$r";
        if ($img) {
            if (!array_key_exists('width', $atts)) {
                $atts['width'] = $s;
            }

            if (!array_key_exists('height', $atts)) {
                $atts['height'] = $s;
            }

            $url = '<img src="' . $url . '"';
            foreach ($atts as $key => $val) {
                $url .= ' ' . $key . '="' . $val . '"';
            }
            $url .= ' />';
        }
        return $url;
    }
}
