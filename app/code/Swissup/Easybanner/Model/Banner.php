<?php
namespace Swissup\Easybanner\Model;

use Swissup\Easybanner\Api\Data\BannerInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Banner extends \Magento\Rule\Model\AbstractModel implements BannerInterface, IdentityInterface
{
    const TYPE_BANNER = 1;
    const TYPE_LIGHTBOX = 2;
    const TYPE_AWESOMEBAR = 3;

    /**
     * cache tag
     */
    const CACHE_TAG = 'banner_';

    /**
     * @var string
     */
    protected $_cacheTag = 'banner_';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'banner_';

    /**
     * @var \Swissup\Easybanner\Model\Rule\Condition\CombineFactory
     */
    protected $combineFactory;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $jsonHelper;

    /**
     * @var \Swissup\Easybanner\Helper\Data
     */
    protected $helper;

    /**
     * @var boolean
     */
    protected $isVisible = null;

    /**
     * @param \Magento\Framework\Model\Context                             $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Framework\Data\FormFactory                          $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface         $localeDate
     * @param \Swissup\Easybanner\Model\Rule\Condition\CombineFactory      $combineFactory
     * @param \Magento\Framework\Stdlib\CookieManagerInterface             $cookie
     * @param \Magento\Framework\Json\Helper\Data                          $jsonHelper
     * @param \Swissup\Easybanner\Helper\Data $helper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param array                                                        $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Swissup\Easybanner\Model\Rule\Condition\CombineFactory $combineFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookie,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Swissup\Easybanner\Helper\Data $helper,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->combineFactory = $combineFactory;
        $this->cookieManager = $cookie;
        $this->jsonHelper = $jsonHelper;
        $this->helper = $helper;

        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Swissup\Easybanner\Model\ResourceModel\Banner');
    }

    /**
     * Prepare data before saving
     *
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

    public function loadByIdentifier($field, $value)
    {
        $this->load($value, $field);
        return $this;
    }

    public function getConditionsInstance()
    {
        return $this->combineFactory->create();
    }

    public function getActionsInstance()
    {
        return $this;
    }

    /**
     * Receive page store ids
     *
     * @return int[]
     */
    public function getStores()
    {
        return $this->hasData('stores') ? $this->getData('stores') : $this->getData('store_id');
    }

    public function getOptionBanners()
    {
        $banners = $this->_getResource()->getOptionBanners();
        $options = [];
        foreach ($banners as $item) {
            $options[] = ['value' => $item['identifier'], 'label' => $item['identifier']];
        }
        return $options;
    }

    /**
     * @return boolean
     */
    public function isPopupType()
    {
        return in_array($this->getType(), [
            self::TYPE_LIGHTBOX,
            self::TYPE_AWESOMEBAR,
        ]);
    }

    /**
     * @return boolean
     */
    public function getTypeCode()
    {
        $mapping = [
            self::TYPE_BANNER => 'banner',
            self::TYPE_LIGHTBOX => 'lightbox',
            self::TYPE_AWESOMEBAR => 'awesomebar',
        ];

        return $mapping[$this->getType()];
    }

    /**
     * Checks all conditions of the banner
     *
     * @return bool
     */
    public function isVisible($storeId)
    {
        if ($this->isVisible !== null) {
            return $this->isVisible;
        }

        if (!$this->getStatus()
            || !is_array($this->getStores())
            || (!in_array($storeId, $this->getStores())
                && !in_array(0, $this->getStores()))
        ) {
            return false;
        }

        if ($this->getDontShowAnymore()) {
            return false;
        }

        // store conditions in case they have client-side conditions
        $conditions = $this->getConditionsSerialized();

        $this->isVisible = $this->validate($this);

        if ($this->isVisible) {
            $conditions = $this->serializer->unserialize($conditions);
            $conditions = $this->getClientSideConditions($conditions);
            $this->setJsConditions($conditions);
        }

        return $this->isVisible;
    }

    /**
     * Extract client-side conditions only
     *
     * @param  array $filter
     * @return array
     */
    private function getClientSideConditions($filter)
    {
        $result = [];

        if (isset($filter['aggregator']) && !empty($filter['conditions'])) {
            $conditions = [];
            foreach ($filter['conditions'] as $_filter) {
                $condition = $this->getClientSideConditions($_filter);
                if ($condition) {
                    $conditions[] = $condition;
                }
            }
            if ($conditions) {
                $result = [
                    'aggregator' => $filter['aggregator'],
                    'value' => $filter['value'],
                    'conditions' => $conditions,
                ];
            }
        } elseif (!empty($filter['attribute'])) {
            $clientConditions = [
                'browsing_time',
                'inactivity_time',
                'activity_time',
                'scroll_offset',
                'exit_intent',
                // Dirty fix for FPC.
                // @todo: remove these conditions, when banners will be
                // loaded through customer/section/load api.
                // @see https://github.com/swissup/easybanner/issues/18
                'display_count_per_customer',
                'display_count_per_customer_per_day',
                'display_count_per_customer_per_week',
                'display_count_per_customer_per_month',
            ];
            if (in_array($filter['attribute'], $clientConditions)) {
                return $filter;
            }
        }

        return $result;
    }

    public function getHtmlId($identifier = null)
    {
        if ($identifier === null) {
            $identifier = $this->getIdentifier();
        }

        return 'banner-' . $this->cleanupName($identifier);
    }

    public function cleanupName($name)
    {
        return preg_replace('/[^a-z0-9_]+/i', '-', $name);
    }

    public function getClicksCount()
    {
        return $this->_getResource()->getClicksCount($this->getId());
    }

    public function getDisplayCount()
    {
        return $this->_getResource()->getDisplayCount($this->getId());
    }

    public function getDontShowAnymore()
    {
        return (int) $this->getCookieValues()->getData($this->getHtmlId() . '/dont_show');
    }

    public function getCookieValues()
    {
        $values = $this->_getData('cookie_values');

        if (null === $values) {
            $data = $this->cookieManager->getCookie($this->helper->getCookieName());
            try {
                $data = $this->jsonHelper->jsonDecode($data);
                if (!$data) {
                    $data = [];
                }
            } catch (\Exception $e) {
                $data = [];
            }
            $values = new \Magento\Framework\DataObject($data);
            $this->setData('cookie_values', $values);
        }

        return $values;
    }

    /**
     * @param string $formName
     * @return string
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }

    public function getIsResponsive()
    {
        if (!$this->getData('is_responsive')) {
            return false;
        }

        return !empty($this->getSizes());
    }

    public function getSizes()
    {
        $sizes = $this->getData('sizes');
        if (is_array($sizes)) {
            return $sizes;
        }

        $this->getResource()->unserializeFields($this);

        return $this->getData('sizes');
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get banner_id
     *
     * @return int
     */
    public function getBannerId()
    {
        return $this->getData(self::BANNER_ID);
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->getData(self::IDENTIFIER);
    }

    /**
     * Get sort_order
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->getData(self::URL);
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->getData(self::IMAGE);
    }

    /**
     * Get html
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->getData(self::HTML);
    }

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Get mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->getData(self::MODE);
    }

    /**
     * Get target
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->getData(self::TARGET);
    }

    /**
     * Get hide_url
     *
     * @return int
     */
    public function getHideUrl()
    {
        return $this->getData(self::HIDE_URL);
    }

    /**
     * Get conditions_serialized
     *
     * @return string
     */
    public function getConditionsSerialized()
    {
        return $this->getData(self::CONDITIONS_SERIALIZED);
    }

    /**
     * Get resize_image
     *
     * @return int
     */
    public function getResizeImage()
    {
        return $this->getData(self::RESIZE_IMAGE);
    }

    /**
     * Get width
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->getData(self::WIDTH);
    }

    /**
     * Get height
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->getData(self::HEIGHT);
    }

    /**
     * Get retina_support
     *
     * @return int
     * @deprecated 1.6.0
     */
    public function getRetinaSupport()
    {
        return $this->getData(self::RETINA_SUPPORT);
    }

    /**
     * Get background_color
     *
     * @return string
     */
    public function getBackgroundColor()
    {
        return $this->getData(self::BACKGROUND_COLOR);
    }

    /**
     * Get class_name
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->getData(self::CLASS_NAME);
    }

    /**
     * Set banner_id
     *
     * @param int $bannerId
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setBannerId($bannerId)
    {
        return $this->setData(self::BANNER_ID, $bannerId);
    }

    /**
     * Set identifier
     *
     * @param string $identifier
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setIdentifier($identifier)
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * Set sort_order
     *
     * @param int $sortOrder
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * Set title
     *
     * @param string $title
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Set url
     *
     * @param string $url
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setUrl($url)
    {
        return $this->setData(self::URL, $url);
    }

    /**
     * Set image
     *
     * @param string $image
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
    }

    /**
     * Set html
     *
     * @param string $html
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setHtml($html)
    {
        return $this->setData(self::HTML, $html);
    }

    /**
     * Set status
     *
     * @param int $status
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Set mode
     *
     * @param string $mode
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setMode($mode)
    {
        return $this->setData(self::MODE, $mode);
    }

    /**
     * Set target
     *
     * @param string $target
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setTarget($target)
    {
        return $this->setData(self::TARGET, $target);
    }

    /**
     * Set hide_url
     *
     * @param int $hideUrl
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setHideUrl($hideUrl)
    {
        return $this->setData(self::HIDE_URL, $hideUrl);
    }

    /**
     * Set conditions_serialized
     *
     * @param string $conditionsSerialized
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setConditionsSerialized($conditionsSerialized)
    {
        return $this->setData(self::CONDITIONS_SERIALIZED, $conditionsSerialized);
    }

    /**
     * Set resize_image
     *
     * @param int $resizeImage
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setResizeImage($resizeImage)
    {
        return $this->setData(self::RESIZE_IMAGE, $resizeImage);
    }

    /**
     * Set width
     *
     * @param int $width
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setWidth($width)
    {
        return $this->setData(self::WIDTH, $width);
    }

    /**
     * Set height
     *
     * @param int $height
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setHeight($height)
    {
        return $this->setData(self::HEIGHT, $height);
    }

    /**
     * Set retina_support
     *
     * @param int $retinaSupport
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setRetinaSupport($retinaSupport)
    {
        return $this->setData(self::RETINA_SUPPORT, $retinaSupport);
    }

    /**
     * Set background_color
     *
     * @param string $backgroundColor
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setBackgroundColor($backgroundColor)
    {
        return $this->setData(self::BACKGROUND_COLOR, $backgroundColor);
    }

    /**
     * Set class_name
     *
     * @param string $className
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setClassName($className)
    {
        return $this->setData(self::CLASS_NAME, $className);
    }
}
