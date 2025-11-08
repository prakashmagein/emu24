<?php

namespace Swissup\Easybanner\Block;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class Placeholder extends Template implements BlockInterface, IdentityInterface
{
    /**
     * @var string
     */
    protected $_template = 'placeholder.phtml';

    /**
     * @var array|null
     */
    protected $_banners;

    /**
     * @var \Swissup\Easybanner\Model\Placeholder
     */
    protected $_placeholderModel;

    /**
     * @var \Swissup\Easybanner\Model\ResourceModel\Banner\Collection
     */
    protected $_bannerCollection;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @param Template\Context $context
     * @param \Swissup\Easybanner\Model\PlaceholderFactory $placeholderModelFactory
     * @param \Swissup\Easybanner\Model\ResourceModel\Banner\CollectionFactory $bannerCollection
     * @param \Magento\Framework\ObjectManagerInterface $_objectManager
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Swissup\Easybanner\Model\PlaceholderFactory $placeholderModelFactory,
        \Swissup\Easybanner\Model\ResourceModel\Banner\CollectionFactory $bannerCollection,
        \Magento\Framework\ObjectManagerInterface $_objectManager,
        array $data = []
    ) {
        $this->_placeholderModel = $placeholderModelFactory->create();
        $this->_bannerCollection = $bannerCollection;
        $this->_objectManager = $_objectManager;
        $this->_storeManager  = $context->getStoreManager();
        parent::__construct($context, $data);
    }

    public function getBanners()
    {
        $placeholder = $this->getPlaceholderModel();
        if (!$placeholder) {
            return false;
        }

        $this->_banners = $this->getBannersToUse();
        if (!count($this->_banners)) {
            return [];
        }

        uasort($this->_banners, [$this, '_sortBanners']);
        $count = count($this->_banners);

        if ($placeholder->getIsRandomSortMode()) {
            $offset = rand(0, $count - 1);
        } else {
            // sort banners according to placeholder offset iterator
            $offset = $placeholder->getBannerOffset();
            $offset = ($count > $offset ? $offset : 0);
        }

        $head = array_splice($this->_banners, $offset);
        $this->_banners = $head + $this->_banners;
        $increment = $placeholder->getLimit();

        if ($count < $increment) {
            $increment = $count;
        }

        $placeholder
            ->setBannerOffset((string)($offset + $increment))
            ->save();

        // limit by placeholder config
        array_splice($this->_banners, $placeholder->getLimit());

        return $this->_banners;
    }

    protected function getPlaceholderModel()
    {
        if ($this->_placeholderModel->getId()) {
            return $this->_placeholderModel->getStatus() ? $this->_placeholderModel : false;
        }

        $placeholderId = $this->getPlaceholder();
        if (!$placeholderId) {
            return false;
        }

        $this->_placeholderModel->load($placeholderId);
        if (!$this->_placeholderModel->getId() || !$this->_placeholderModel->getStatus()) {
            return false;
        }

        return $this->_placeholderModel;
    }

    protected function getBannersToUse()
    {
        if ($this->_banners !== null) {
            return $this->_banners;
        }

        $this->_banners = [];

        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $_bannerCollection = $this->_bannerCollection->create();

        $_bannerCollection->getSelect()
            ->joinInner(
                ['placeholder' => $resource->getTableName('swissup_easybanner_banner_placeholder')],
                'placeholder.banner_id=main_table.banner_id and placeholder.placeholder_id=' . $this->_placeholderModel->getId()
            )
            ->where('status = ?', 1)
            ->where('type = ?', 1);

        $bannerCss = $this->getBannerCssClass();
        $storeId = $this->_storeManager->getStore()->getId();
        $skipVisibilityCheck = $this->hasStoreId() && !$this->getStoreId();
        foreach ($_bannerCollection->getItems() as $banner) {
            if (!$skipVisibilityCheck && !$banner->isVisible($storeId)) {
                continue;
            }

            if ($skipVisibilityCheck) {
                $banner->setStoreId(0);
            }

            if ($bannerCss) {
                $banner->setAdditionalCssClass($bannerCss);
            }
            $this->_banners[$banner->getIdentifier()] = $banner;
        }

        return $this->_banners;
    }

    private function _sortBanners($a, $b)
    {
        if ($a['sort_order'] == $b['sort_order']) {
            return 0;
        }
        return $a['sort_order'] < $b['sort_order'] ? -1 : 1;
    }

    public function getPlaceholderClassName()
    {
        $class = 'placeholder-' . $this->cleanupName($this->_placeholderModel->getName());

        if ($this->getAdditionalCssClass()) {
            $class .= ' ' . $this->getAdditionalCssClass();
        }

        if (($additionalClass = $this->_placeholderModel->getAdditionalCssClass()) &&
            strpos($class, $additionalClass) === false
        ) {
            $class .= ' ' . $additionalClass;
        }

        return $class;
    }

    public function cleanupName($name)
    {
        return preg_replace('/[^a-z0-9_]+/i', '-', $name);
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        if (!$this->getPlaceholderModel()) {
            return [];
        }

        $identities = [
            \Swissup\Easybanner\Model\Placeholder::CACHE_TAG
            . '_'
            . $this->getPlaceholderModel()->getId()
        ];

        foreach ($this->getBannersToUse() as $banner) {
            $identities[] = \Swissup\Easybanner\Model\Banner::CACHE_TAG . '_' . $banner->getId();
        }

        return $identities;
    }
}
