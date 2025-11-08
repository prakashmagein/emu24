<?php

namespace Swissup\Attributepages\Block\Product;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Swissup\Attributepages\Model\Entity as AttributepagesModel;

class Option extends Template implements BlockInterface, IdentityInterface
{
    const IMAGE_WIDTH  = 30;
    const IMAGE_HEIGHT = 15;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Swissup\Attributepages\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Swissup\Attributepages\Helper\Product
     */
    protected $attrpagesProductHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Swissup\Attributepages\Helper\Image $imageHelper
     * @param \Swissup\Attributepages\Helper\Product $attrpagesProductHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Swissup\Attributepages\Helper\Image $imageHelper,
        \Swissup\Attributepages\Helper\Product $attrpagesProductHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $coreRegistry;
        $this->imageHelper = $imageHelper;
        $this->attrpagesProductHelper = $attrpagesProductHelper;
    }

    /**
     * Get template path to render
     *
     * @return string
     */
    public function getTemplate()
    {
        $template = parent::getTemplate();
        if (null === $template) {
            $template = $this->_getData('block_template');
        }
        return $template;
    }

    /**
     * Retrieve product to use for output
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        $product = $this->_getData('product');
        if (null === $product) {
            $product = $this->coreRegistry->registry('current_product');
            $this->setData('product', $product);
        }
        return $product;
    }

    /**
     * Returns attributepage options to render
     *
     * @return array
     */
    public function getOptions()
    {
        if ($this->getProduct() && $this->getAttributeCode()) {
            $attributeCode = $this->getAttributeCode();
            if (!is_array($attributeCode)) {
                $attributeCode = str_replace(' ', '', $attributeCode);
                $attributeCode = explode(',', $attributeCode);
            }

            $isLoaded = false;
            $loaded = $this->getProduct()->getAttributepages();
            if (null !== $loaded) {
                $notLoaded = array_diff($attributeCode, array_keys($loaded));
                if (!$notLoaded) {
                    $isLoaded = true;
                }
            }

            if (!$this->getAttributeToShow()) {
                $this->setAttributeToShow($attributeCode);
            }

            if (!$isLoaded) {
                $this->attrpagesProductHelper->appendPages(
                    $this->getProduct(),
                    $attributeCode,
                    $this->getParentPageIdentifier()
                );
            }
        }

        if ($this->getProduct() && $this->getProduct()->getAttributepages()) {
            $options = $this->getProduct()->getAttributepages();
            $visible = $this->getAttributeToShow();
            if ($visible) {
                if (!is_array($visible)) {
                    $visible = explode(',', $visible);
                }
                $options = array_intersect_key($options, array_flip($visible));
            }

            $hidden = $this->getAttributeToHide();
            if ($hidden) {
                if (!is_array($hidden)) {
                    $hidden = explode(',', $hidden);
                }
                $options = array_diff_key($options, array_flip($hidden));
            }

            return array_filter($options); // remove nulls
        }

        return [];
    }

    /**
     * Get image width
     *
     * @return int
     */
    public function getWidth()
    {
        $width = $this->_getData('width');
        if (null === $width) {
            return self::IMAGE_WIDTH;
        }
        return $width;
    }

    /**
     * Get image height
     *
     * @return int
     */
    public function getHeight()
    {
        $height = $this->_getData('height');
        if (null === $height) {
            return self::IMAGE_HEIGHT;
        }
        return $height;
    }

    /**
     * Get custom title to the parent page
     *
     * @param  \Swissup\Attributepages\Model\Entity $attributepage
     * @return mixed
     */
    public function getParentPageLinkTitle(?\Swissup\Attributepages\Model\Entity $attributepage = null)
    {
        return $this->_getPageData('parent_page_link_title', $attributepage);
    }

    /**
     * @return \Swissup\Attributepages\Helper\Image
     */
    public function getImageHelper()
    {
        $helper = $this->imageHelper;
        if (null !== $this->getKeepFrame()) {
            $helper->setKeepFrame($this->getKeepFrame());
        }
        return $helper;
    }

    public function getImageType()
    {
        $type = $this->_getData('image_type');
        if (!$type) {
            return 'thumbnail';
        }
        return $type;
    }

    /**
     * Retieve flag, that indicates if the image should be shown
     *
     * @return boolean
     */
    public function getUseImage(?\Swissup\Attributepages\Model\Entity $attributepage = null)
    {
        $flag = $this->_getPageData('use_image', $attributepage);
        if (null === $flag) {
            return true;
        }
        return $flag;
    }

    /**
     * Retieve flag, that indicates if the link should be used
     *
     * @return boolean
     */
    public function getUseLink(?\Swissup\Attributepages\Model\Entity $attributepage = null)
    {
        $flag = $this->_getPageData('use_link', $attributepage);
        if (null === $flag) {
            return true;
        }
        return $flag;
    }

    /**
     * Retrieve data for particular page
     *
     * @param  string
     * @param  \Swissup\Attributepages\Model\Entity $attributepage
     * @return mixed
     */
    protected function _getPageData($key, ?\Swissup\Attributepages\Model\Entity $attributepage = null)
    {
        $data = $this->_getData($key);

        if (!$data || null === $attributepage || !is_array($data)) {
            return $data;
        }

        if (!$attributepage->getAttributeCode()
            || !isset($data[$attributepage->getAttributeCode()])) {
            return null;
        }

        return $data[$attributepage->getAttributeCode()];
    }

    public function getCssClass()
    {
        $classes = [];
        $classes[] = $this->getData('css_class');
        $classes[] = $this->getUseShortDescription() ? 'with-description' : '';

        return implode(' ', array_filter($classes));
    }

    public function getIdentities()
    {
        $result = [];

        foreach ($this->getOptions() as $groupedOptions) {
            $option = current($groupedOptions);
            if ($parent = $option->getParentPage()) {
                $result[] = AttributepagesModel::CACHE_TAG . '_' . $parent->getId();
            }
        }

        return $result;
    }
}
