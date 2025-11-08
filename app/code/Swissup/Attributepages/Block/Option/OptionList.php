<?php
namespace Swissup\Attributepages\Block\Option;

use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\Context;

class OptionList extends \Swissup\Attributepages\Block\AbstractBlock
{
    /**
     * @var \Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory
     */
    public $attrpagesCollectionFactory;

    /**
     * @var \Swissup\Attributepages\Model\ResourceModel\Entity\Collection
     */
    protected $optionCollection;

    /**
     * @var \Swissup\Attributepages\Helper\OptionGroup
     */
    protected $optionGroupHelper;

    /**
     * @var \Swissup\Attributepages\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory $attrpagesCollectionFactory
     * @param \Swissup\Attributepages\Helper\OptionGroup $optionGroupHelper
     * @param \Swissup\Attributepages\Helper\Image $imageHelper
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory $attrpagesCollectionFactory,
        \Swissup\Attributepages\Helper\OptionGroup $optionGroupHelper,
        \Swissup\Attributepages\Helper\Image $imageHelper,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        parent::__construct($context, $coreRegistry, $attrpagesCollectionFactory, $data);
        $this->optionGroupHelper = $optionGroupHelper;
        $this->imageHelper = $imageHelper;
        $this->httpContext = $httpContext;
    }

    /**
     * Initialize block's cache
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addData([
            'cache_lifetime' => false
        ]);
    }

    /**
     * Retrieve loaded category collection
     *
     * @return \Swissup\Attributepages\Model\ResourceModel\Entity\Collection
     */
    protected function getOptionCollection()
    {
        if (null === $this->optionCollection && $this->getCurrentPage()) {
            $storeId = $this->_storeManager->getStore()->getId();
            $parentPage = $this->getCurrentPage();
            $this->optionCollection = $this->attrpagesCollectionFactory->create()
                ->addOptionOnlyFilter()
                ->addFieldToFilter('attribute_id', $parentPage->getAttributeId())
                ->addUseForAttributePageFilter()
                ->addStoreFilter($storeId);

            $this->optionCollection
                ->setOrder(
                    (string) $this->optionCollection->getConnection()
                        ->getIfNullSql('main_table.title', 'main_table.name'),
                    'asc'
                )
                ->setOrder('store_id', 'asc');

            if ($excludedOptions = $parentPage->getExcludedOptionIdsArray()) {
                $this->optionCollection
                    ->addFieldToFilter('option_id', [
                        'nin' => $excludedOptions
                    ]);
            }

            if ($options = $this->getOptionsToShow()) {
                $this->optionCollection->addFieldToFilter('identifier', [
                    'in' => explode(',', $options),
                ]);
            }

            if ($options = $this->getOptionsToHide()) {
                $this->optionCollection->addFieldToFilter('identifier', [
                    'nin' => explode(',', $options),
                ]);
            }

            // filter options with the same urls: linked to All Store Views and current store
            $urls = $this->optionCollection->getColumnValues('identifier');
            $duplicateUrls = [];
            foreach (array_count_values($urls) as $url => $count) {
                if ($count > 1) {
                    $duplicateUrls[] = $url;
                }
            }

            foreach ($duplicateUrls as $url) {
                $idsToRemove = [];
                $removeFlag = false;

                $options = $this->optionCollection->getItemsByColumnValue('identifier', $url);
                foreach ($options as $option) {
                    if (!in_array($storeId, $option->getStores())) {
                        $idsToRemove[] = $option->getId();
                    } else {
                        $removeFlag = true;
                    }
                }

                if ($removeFlag) {
                    foreach ($idsToRemove as $id) {
                        $this->optionCollection->removeItemByKey($id);
                    }
                }
            }

            // do not use mysql limit because of duplicates
            if ($limit = $this->getLimit()) {
                $i = 0;
                foreach ($this->optionCollection as $key => $option) {
                    if ($i++ >= $limit) {
                        $this->optionCollection->removeItemByKey($key);
                    }
                }
            }

            foreach ($this->optionCollection as $option) {
                $option->setParentPage($parentPage);
            }

            $this->optionCollection->sortByRenderedTitle(
                !$this->getGroupByFirstLetter()
            );
        }

        return $this->optionCollection;
    }

    /**
     * Retrieve loaded category collection
     *
     * @return \Swissup\Attributepages\Model\ResourceModel\Entity\Collection
     */
    public function getLoadedOptionCollection()
    {
        return $this->getOptionCollection();
    }

    /**
     * Retrieve current view mode
     *
     * @return string
     */
    public function getListingMode()
    {
        return $this->getConfigurableParam('listing_mode');
    }

    public function getColumnCount()
    {
        return $this->getConfigurableParam('column_count', 4);
    }

    public function getImageWidth()
    {
        return $this->getConfigurableParam('image_width', 200);
    }

    public function getImageHeight()
    {
        return $this->getConfigurableParam('image_height', 150);
    }

    public function getGroupByFirstLetter()
    {
        return $this->getConfigurableParam('group_by_first_letter');
    }

    public function getSliderId()
    {
        $key  = 'slider_id';
        $data = $this->_getData($key);
        if (null === $data) {
            $this->setData($key, $this->getCurrentPage()->getIdentifier());
        }
        return $this->_getData($key);
    }

    public function getSliderConfig()
    {
        $params = [
            'slidesPerView' => $this->getSlidesToShow(),
            'slidesToScroll' => $this->getSlidesToScroll(),
            'spaceBetween' => 10,
            'freeMode' => true,
            'loop' => true,
            'navigation' => [
                'nextEl' => '.swiper-button-next',
                'prevEl' => '.swiper-button-prev',
            ],
            'breakpoints' => [
                '375' => [
                    'slidesPerView' => $this->getSlidesToShow() > 3 ? 3 : $this->getSlidesToShow(),
                ],
                '480' => [
                    'slidesPerView' => $this->getSlidesToShow() > 4 ? 4 : $this->getSlidesToShow(),
                ],
                '768' => [
                    'slidesPerView' => $this->getSlidesToShow() > 6 ? 6 : $this->getSlidesToShow(),
                ],
            ],
        ];

        if ($this->getAutoplay()) {
            $params['autoplay'] = [
                'delay' => 5000
            ];
        }

        return json_encode($params, JSON_HEX_APOS);
    }

    public function setCollection($collection)
    {
        $this->optionCollection = $collection;
        return $this;
    }

    /**
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cacheKeyInfo = [
            'SWISSUP_ATTRIBUTEPAGES_OPTION_LIST',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(Context::CONTEXT_GROUP),
            'template' => $this->getTemplate(),
            'name' => $this->getNameInLayout(),
            $this->getListingMode(),
            $this->getColumnCount(),
            $this->getImageWidth(),
            $this->getImageHeight(),
            $this->getGroupByFirstLetter(),
        ];

        if ($this->getCurrentPage()) {
            $cacheKeyInfo[] = $this->getCurrentPage()->getIdentifier();
            $cacheKeyInfo[] = $this->getSliderId();
        }

        return $cacheKeyInfo;
    }

    /**
     * @return \Swissup\Attributepages\Helper\OptionGroup
     */
    public function getOptionGroupHelper()
    {
        return $this->optionGroupHelper;
    }

    /**
     * @return \Swissup\Attributepages\Helper\Image
     */
    public function getImageHelper()
    {
        return $this->imageHelper;
    }
}
