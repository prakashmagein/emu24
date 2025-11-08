<?php
namespace Swissup\Attributepages\Model;

use Swissup\Attributepages\Api\Data\EntityInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Rule\Model\AbstractModel;

class Entity extends AbstractModel implements EntityInterface, IdentityInterface
{
    private $combineFactory;
    private $actionCollectionFactory;
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    const DISPLAY_MODE_MIXED       = 'mixed';
    const DISPLAY_MODE_DESCRIPTION = 'description';
    const DISPLAY_MODE_CHILDREN    = 'children';

    const LISTING_MODE_IMAGE = 'image';
    const LISTING_MODE_LINK  = 'link';
    const LISTING_MODE_GRID  = 'grid';
    const LISTING_MODE_LIST  = 'list';

    const DELIMITER = ',';

    const CACHE_TAG = 'attributepage';

    /**
     * @var string
     */
    protected $_cacheTag = 'attributepages_entity';

    /**
     * @var string
     */
    protected $_eventPrefix = 'attributepages_entity';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $attrCollectionFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory
     */
    protected $attrOptionCollectionFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $coreResource;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory
     */
    protected $attrpagesCollectionFactory;

    /**
     * @var \Swissup\Attributepages\Helper\Data
     */
    protected $attrpagesHelper;

    /**
     * @var \Swissup\Attributepages\Helper\Product
     */
    protected $attrpagesProductHelper;

    /**
     * @var \Magento\Widget\Model\Template\Filter
     */
    protected $filter;

    protected \Magento\Framework\App\State $appState;

    /**
     * @var array
     */
    protected $memo = [
        'options' => [],
        'title' => '',
        'page_title' => '',
    ];

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Swissup\Attributepages\Model\PageRule\ConditionsCombineFactory $combineFactory,
        \Magento\CatalogRule\Model\Rule\Action\CollectionFactory $actionCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attrCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Framework\App\ResourceConnection $coreResource,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\UrlInterface $url,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory $attrpagesCollectionFactory,
        \Swissup\Attributepages\Helper\Data $attrpagesHelper,
        \Swissup\Attributepages\Helper\Product $attrpagesProductHelper,
        \Magento\Widget\Model\Template\Filter $filter,
        \Magento\Framework\App\State $appState,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->combineFactory = $combineFactory;
        $this->actionCollectionFactory = $actionCollectionFactory;
        $this->attrCollectionFactory = $attrCollectionFactory;
        $this->attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->coreResource = $coreResource;
        $this->jsonHelper = $jsonHelper;
        $this->url = $url;
        $this->storeManager = $storeManager;
        $this->attrpagesCollectionFactory = $attrpagesCollectionFactory;
        $this->attrpagesHelper = $attrpagesHelper;
        $this->attrpagesProductHelper = $attrpagesProductHelper;
        $this->filter = $filter;
        $this->appState = $appState;
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Swissup\Attributepages\Model\ResourceModel\Entity::class);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        if ($this->getConditions()) {
            $this->setConditionsSerialized(
                $this->serializer->serialize($this->getConditions()->asArray())
            );
            $this->unsConditions();
        }

        return $this;
    }

    /**
     * @return integer
     */
    public function getCategoryId()
    {
        foreach ($this->getAdditionalFilters() as $condition) {
            if ($condition->getAttribute() === 'category_ids') {
                return (int) $condition->getValue();
            }
        }

        return (int) $this->storeManager->getStore()->getRootCategoryId();
    }

    /**
     * @return array
     */
    public function getAttributeFilters()
    {
        $filters = [];

        foreach ($this->getAdditionalFilters() as $condition) {
            if ($condition->getAttribute() === 'category_ids') {
                continue;
            }
            $filters[$condition->getAttribute()] = $condition->getValue();
        }

        return $filters;
    }

    /**
     * @return array
     */
    public function getAdditionalFilters()
    {
        $conditions = $this->getConditions()->getConditions();
        $parentPage = $this->getParentPage();

        if (!$conditions && $parentPage) {
            $conditions = $parentPage->getConditions()->getConditions();
        }

        return $conditions;
    }

    /**
     * @param string $formName
     * @return string
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }

    /**
     * @return \Swissup\Attributepages\Model\PageRule\ConditionsCombine
     */
    public function getConditionsInstance()
    {
        return $this->combineFactory->create();
    }

    /**
     * @return \Magento\CatalogRule\Model\Rule\Action\Collection
     */
    public function getActionsInstance()
    {
        return $this->actionCollectionFactory->create();
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        $identities = [self::CACHE_TAG . '_' . $this->getId()];

        if ($parent = $this->getParentPage()) {
            $identities = array_merge($identities, $parent->getIdentities());
        }

        return $identities;
    }

    /**
     * @return int
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @return int
     */
    public function getAttributeId()
    {
        return $this->getData(self::ATTRIBUTE_ID);
    }

    /**
     * @return int
     */
    public function getOptionId()
    {
        return $this->getData(self::OPTION_ID);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->getData(self::IDENTIFIER);
    }

    /**
     * @param string $string
     * @return string
     */
    protected function processTemplate($string)
    {
        if (!$string || strpos($string, '{{') === false) {
            return $string;
        }

        $attribute = $this->getAttribute();
        $option = $this->getOption();

        return $this->filter
            ->setVariables([
                'attribute' => [
                    'label' => $attribute ? $attribute->getStoreLabel() : '',
                    'code' => $attribute ? $attribute->getAttributeCode() : '',
                ],
                'option' => [
                    'label' => $option ? $option->getLabel() : '',
                ],
            ])
            ->filter($string);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        if (!$this->memo['title']) {
            $title = $this->getData(self::TITLE);

            if (!$title && $parent = $this->getParentPage()) {
                $title = $parent->getData('options_title');
            }

            $this->memo['title'] = $this->processTemplate($title);
        }

        return $this->memo['title'];
    }

    /**
     * @return string
     */
    public function getPageTitle()
    {
        if (!$this->memo['page_title']) {
            $pageTitle = $this->processTemplate($this->getData(self::PAGE_TITLE));

            if (empty($pageTitle)) {
                $pageTitle = $this->getTitle();
            }

            $this->memo['page_title'] = $pageTitle;
        }

        return $this->memo['page_title'];
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->getData(self::CONTENT);
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->getData(self::IMAGE);
    }

    /**
     * @return string
     */
    public function getThumbnail()
    {
        return $this->getData(self::THUMBNAIL);
    }

    /**
     * @return string
     */
    public function getMetaTitle()
    {
        return $this->getData(self::META_TITLE);
    }

    /**
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->getData(self::META_KEYWORDS);
    }

    /**
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->getData(self::META_DESCRIPTION);
    }

    /**
     * @return string
     */
    public function getDisplaySettings()
    {
        return $this->getData(self::DISPLAY_SETTINGS);
    }

    /**
     * @return string|null
     */
    public function getRootTemplate()
    {
        $template = $this->getData(self::ROOT_TEMPLATE);

        if ($template !== null) {
            return $template;
        }

        if ($parent = $this->getParentPage()) {
            return $parent->getOptionsRootTemplate();
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getDisplayMode()
    {
        $mode = $this->getData(self::DISPLAY_MODE);

        if ($mode !== null) {
            return $mode;
        }

        if ($parent = $this->getParentPage()) {
            return $parent->getOptionsDisplayMode();
        }

        return null;
    }

    /**
     * @return string
     */
    public function getLayoutUpdateXml()
    {
        return $this->getData(self::LAYOUT_UPDATE_XML);
    }

    /**
     * @return int
     */
    public function getUseForAttributePage()
    {
        return $this->getData(self::USE_FOR_ATTRIBUTE_PAGE);
    }

    /**
     * @return int
     */
    public function getUseForProductPage()
    {
        return $this->getData(self::USE_FOR_PRODUCT_PAGE);
    }

    /**
     * @return string
     */
    public function getExcludedOptionIds()
    {
        return $this->getData(self::EXCLUDED_OPTION_IDS);
    }

    /**
     * @param int $entityId
     * @return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * @param int $attributeId
     * @return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setAttributeId($attributeId)
    {
        return $this->setData(self::ATTRIBUTE_ID, $attributeId);
    }

    /**
     * @param int $optionId
     * @return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setOptionId($optionId)
    {
        return $this->setData(self::OPTION_ID, $optionId);
    }

    /**
     * @param string $name
     * @return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @param string $identifier
     * @return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setIdentifier($identifier)
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * @param string $title
     * @return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @param string $pageTitle
     * @return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setPageTitle($pageTitle)
    {
        return $this->setData(self::PAGE_TITLE, $pageTitle);
    }

    /**
     * @param string $content
     * @return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }

    /**
     * @param string $image
     * @return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
    }

    /**
     * @param string $thumbnail
     * @return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setThumbnail($thumbnail)
    {
        return $this->setData(self::THUMBNAIL, $thumbnail);
    }

    /**
     * @param string $metaKeywords
     * @return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setMetaKeywords($metaKeywords)
    {
        return $this->setData(self::META_KEYWORDS, $metaKeywords);
    }

    /**
     * @param string $metaDescription
     * @return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setMetaDescription($metaDescription)
    {
        return $this->setData(self::META_DESCRIPTION, $metaDescription);
    }

    /**
     * @param string $displaySettings
     * @return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setDisplaySettings($displaySettings)
    {
        return $this->setData(self::DISPLAY_SETTINGS, $displaySettings);
    }

    /**
     * @param string $rootTemplate
     * @return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setRootTemplate($rootTemplate)
    {
        return $this->setData(self::ROOT_TEMPLATE, $rootTemplate);
    }

    /**
     * @param string $layoutUpdateXml
     * @return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setLayoutUpdateXml($layoutUpdateXml)
    {
        return $this->setData(self::LAYOUT_UPDATE_XML, $layoutUpdateXml);
    }

    /**
     * @param int $useForAttributePage
     * @return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setUseForAttributePage($useForAttributePage)
    {
        return $this->setData(self::USE_FOR_ATTRIBUTE_PAGE, $useForAttributePage);
    }

    /**
     * @param int $useForProductPage
     * @return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setUseForProductPage($useForProductPage)
    {
        return $this->setData(self::USE_FOR_PRODUCT_PAGE, $useForProductPage);
    }

    /**
     * @param string $excludedOptionIds
     * @return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setExcludedOptionIds($excludedOptionIds)
    {
        return $this->setData(self::EXCLUDED_OPTION_IDS, $excludedOptionIds);
    }

    /**
     * @return int[]
     */
    public function getStores()
    {
        return $this->hasData('stores') ? $this->getData('stores') : $this->getData('store_id');
    }

    /**
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ENABLED => __('Enabled'),
            self::STATUS_DISABLED => __('Disabled'),
        ];
    }

    /**
     * @return boolean
     */
    public function isAttributeBasedPage()
    {
        return !(bool)$this->getOptionId();
    }

    /**
     * @return boolean
     */
    public function isOptionBasedPage()
    {
        return (bool)$this->getOptionId();
    }

    /**
     * @return Mage_Eav_Model_Entity_Attribute
     */
    public function getAttribute()
    {
        if ($parent = $this->getParentPage()) {
            return $parent->getAttribute();
        }

        $attribute = $this->_getData('attribute');

        if (!$attribute && $this->getAttributeId()) {
            $attribute = $this->attrCollectionFactory->create()
                ->addFieldToFilter('main_table.attribute_id', $this->getAttributeId())
                ->getFirstItem();

            if ($attribute) {
                $this->setData('attribute', $attribute);
            }
        }

        return $this->_getData('attribute');
    }

    /**
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection
     */
    public function getRelatedOptions()
    {
        $options = $this->_getData('related_options');

        if (!$options && $this->isAttributeBasedPage()) {
            $options = $this->attrOptionCollectionFactory->create()
                ->setAttributeFilter($this->getAttributeId());

            $table = $this->coreResource->getTableName('eav_attribute_option_value');
            $options->getSelect()
                ->reset('columns')
                ->columns(['option_id', 'attribute_id'])
                ->joinLeft(
                    ['sort_alpha_value' => $table],
                    'sort_alpha_value.option_id = main_table.option_id AND sort_alpha_value.store_id = 0',
                    ['value']
                );
        }

        return $options;
    }

    public function getExcludedOptionIdsArray()
    {
        $ids = $this->getExcludedOptionIds();

        if (!$ids) {
            $ids = [];
        } else if (!is_array($ids)) {
            $ids = explode(self::DELIMITER, $ids);
        }

        return $ids;
    }

    public function importOptionData($option, $applyDefaults = true)
    {
        $this->setAttributeId($option->getAttributeId())
            ->setOptionId($option->getOptionId())
            ->setName($option->getValue());

        $identifier = $option->getValue();
        if (function_exists('mb_strtolower')) {
            $identifier = mb_strtolower($identifier, 'UTF-8');
        }

        $this->setIdentifier($identifier);
        if ($applyDefaults) {
            $this->setDisplayMode(self::DISPLAY_MODE_MIXED)
                ->setStores([\Magento\Store\Model\Store::DEFAULT_STORE_ID]);
        }

        return $this;
    }

    /**
     * Overriden to convert the json saved display settings to array style
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setData($key, $value = null)
    {
        parent::setData($key, $value);

        if ((is_array($key) && array_key_exists('display_settings', $key))
            || 'display_settings' === $key
        ) {
            if (is_array($key)) {
                $value = $key['display_settings'];
            }

            try {
                $config = $this->jsonHelper->jsonDecode($value);
            } catch (\Exception $e) {
                $config = [];
            }

            foreach ($config as $key => $value) {
                parent::setData($key, $value);
            }
        }

        if ($key === 'store_id') {
            if (!$value) {
                $value = [];
            }
            parent::setData('stores', is_array($value) ? $value : [$value]);
        }

        return $this;
    }

    public function getUrl($absolute = true)
    {
        $url = $this->getIdentifier();

        if ($parent = $this->getParentPage()) {
            $url = $parent->getIdentifier() . '/' . $url;
        }

        if ($absolute) {
            $url = $this->url->getUrl($url);
        }

        return rtrim($url, '/') . $this->attrpagesHelper->getUrlSuffix();
    }

    public function getRelativeUrl()
    {
        return $this->getUrl(false);
    }

    /**
     * @return mixed
     */
    public function getParentPage($storeId = null)
    {
        if ($this->isAttributeBasedPage()) {
            return false;
        }

        $parentPage = $this->getData('parent_page');

        if ($parentPage === null) {
            $storeId = $storeId ?: $this->storeManager->getStore()->getId();
            $collection = $this->attrpagesCollectionFactory->create()
                ->addAttributeOnlyFilter()
                ->addFieldToFilter('attribute_id', $this->getAttributeId());

            if ($this->appState->getAreaCode() !== \Magento\Framework\App\Area::AREA_ADMINHTML) {
                $collection
                    ->addUseForAttributePageFilter() // enabled flag
                    ->addStoreFilter($storeId);
            }

            if ($identifier = $this->getParentPageIdentifier()) {
                $collection->addFieldToFilter('identifier', $identifier);
            }

            $parentPage = $this->attrpagesProductHelper->findParentPage(
                $this,
                $collection,
                $storeId,
                $this->getParentPageIdentifier()
            );

            $this->setData('parent_page', $parentPage);
        }

        return $parentPage;
    }

    /**
     * @return \Magento\Eav\Model\Entity\Attribute\Option|false
     */
    public function getOption($id = null)
    {
        if ($parent = $this->getParentPage()) {
            return $parent->getOption($this->getOptionId());
        } elseif (!$id) {
            $id = $this->getOptionId();
        }

        if (!$this->memo['options'] && $id) {
            $collection = $this->getAttribute()
                ->setStoreId($this->storeManager->getStore()->getId())
                ->getOptions();

            foreach ($collection as $option) {
                $this->memo['options'][$option->getValue()] = $option;
            }
        }

        return $this->memo['options'][$id] ?? false;
    }

    /**
     * @return boolean
     */
    public function isMixedMode()
    {
        return $this->getDisplayMode() == self::DISPLAY_MODE_MIXED;
    }

    /**
     * @return boolean
     */
    public function isDescriptionMode()
    {
        return $this->getDisplayMode() == self::DISPLAY_MODE_DESCRIPTION;
    }

    /**
     * @return boolean
     */
    public function isChildrenMode()
    {
        return $this->getDisplayMode() == self::DISPLAY_MODE_CHILDREN;
    }

    /**
     * @return boolean
     */
    public function canUseLayeredNavigation()
    {
        $result = $this->getData('use_layered_navigation');

        if ($result !== null) {
            return (bool) $result;
        }

        if ($parent = $this->getParentPage()) {
            return $parent->canUseLayeredNavigation();
        }

        return true;
    }
}
