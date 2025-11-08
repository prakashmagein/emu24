<?php

namespace Swissup\Navigationpro\Model;

use Swissup\Navigationpro\Api\Data\ItemInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Item extends AbstractEntity implements ItemInterface, IdentityInterface
{
    const CACHE_TAG = 'NAVIGATIONPRO_ITEM';

    const REMOTE_ENTITY_TYPE_CATEGORY = 1;

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var integer
     */
    protected $storeId = 0;

    /**
     * @var Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Swissup\Navigationpro\Model\ItemFactory
     */
    protected $itemFactory;

    /**
     * @var Swissup\Navigationpro\Model\ResourceModel\Item\CollectionFactory
     */
    protected $itemCollectionFactory;


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Swissup\Navigationpro\Model\ItemFactory $itemFactory,
        \Swissup\Navigationpro\Model\ResourceModel\Item\CollectionFactory $itemCollectionFactory,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->itemFactory = $itemFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;

        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Swissup\Navigationpro\Model\ResourceModel\Item');
    }

    /**
     * Retrieve parent item
     *
     * @return \Swissup\Navigationpro\Model\Item
     */
    public function getParentItem()
    {
        if (!$this->hasData('parent_item')) {
            $parentItem = $this->itemFactory->create();
            if ($this->getParentId()) {
                $parentItem->load($this->getParentId());
            } else {
                $parentItem->addData([
                    'menu_id' => $this->getMenuId(),
                    'level' => 1,
                    'path'  => 0
                ]);
            }
            $this->setData('parent_item', $parentItem);
        }
        return $this->_getData('parent_item');
    }

    /**
     * Retrieve children items
     *
     * @return \Swissup\Navigationpro\Model\Item\Collection
     */
    public function getChildrenItems()
    {
        return $this->itemCollectionFactory->create()
            ->addFieldToFilter('menu_id', $this->getMenuId())
            ->addFilterByParentId($this->getId());
    }

    /**
     * Get last child item
     *
     * @return \Swissup\Navigationpro\Model\Item
     */
    public function getLastChildItem()
    {
        if (!$this->hasData('last_child_items')) {
            $collection = $this->itemCollectionFactory->create()
                ->addFieldToFilter('menu_id', $this->getMenuId())
                ->addFilterByParentId($this->getId())
                ->setOrder('position', 'DESC')
                ->setOrder('item_id', 'DESC')
                ->setPageSize(1);

            $this->setData('last_child_items', $collection->getFirstItem());
        }
        return $this->_getData('last_child_items');
    }

    /**
     * Retreive next siblings
     *
     * @return \Swissup\Navigationpro\Model\Item\Collection
     */
    public function getNextSiblingItems()
    {
        if (!$this->hasData('next_sibling_items')) {
            $collection = $this->itemCollectionFactory->create()
                ->addFieldToFilter('menu_id', $this->getMenuId())
                ->addFieldToFilter('item_id', ['neq' => $this->getId()])
                ->addFilterByParentId($this->getParentId())
                ->setOrder('position', 'ASC')
                ->setOrder('item_id', 'ASC');


            $connection = $collection->getConnection();
            $collection->getSelect()->where(
                sprintf(
                    "%s OR (%s AND %s)",
                    $connection->quoteInto('position > ?', $this->getPosition()),
                    $connection->quoteInto('position = ?', $this->getPosition()),
                    $connection->quoteInto('item_id > ?', $this->getId())
                )
            );

            $this->setData('next_sibling_items', $collection);
        }
        return $this->_getData('next_sibling_items');
    }

    /**
     * Checks if item represents catalog category
     *
     * @return boolean
     */
    public function isCategoryItem()
    {
        return self::REMOTE_ENTITY_TYPE_CATEGORY == $this->getRemoteEntityType();
    }

    /**
     * Get item name
     *
     * @return string
     */
    public function getName()
    {
        $name = $this->getData(self::NAME);
        if ($this->getRemoteEntity() && $this->getUseRemoteData()) {
            $name = $this->getRemoteEntity()->getName();
        }
        return $name;
    }

    /**
     * Get item url
     *
     * @return string
     */
    public function getUrl()
    {
        if ($this->getRemoteEntity() && $this->getUseRemoteData()) {
            return $this->getRemoteEntity()->getUrl();
        }

        $url = (string) $this->getData(self::URL_PATH);
        if (strpos($url, '#') === 0
            || strpos($url, 'tel:') === 0
            || strpos($url, 'mailto:') === 0
            || strpos($url, 'http://') === 0
            || strpos($url, 'https://') === 0
        ) {
            return $url;
        }

        return $this->urlBuilder->getDirectUrl($url);
    }

    /**
     * Merge default and store_view data into $item
     * @param  array $default
     * @param  array $scope
     * @return $this
     */
    public function addContentData($default, $scope)
    {
        // use default data as default, but override it with scope-data, if available
        $this->addData(array_merge(
            $default,
            array_filter($scope, function ($value) {
                return $value !== null;
            })
        ));

        // same as above, but for complex json columns:
        $jsonColumns = ['dropdown_settings'];
        foreach ($jsonColumns as $key) {
            if (!isset($default[$key]) || !isset($scope[$key])) {
                continue;
            }

            $this->setData($key, array_merge(
                $default[$key],
                array_filter($scope[$key], function ($value) {
                    return $value !== null;
                })
            ));
        }

        return $this;
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     */
    public function addCategoryEntityData($category)
    {
        return $this->addData([
            'remote_entity_type' => self::REMOTE_ENTITY_TYPE_CATEGORY,
            'remote_entity_id' => $category->getId(),
            'store_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            'is_active' => ($category->getIsActive() && $category->getIncludeInMenu()),
            'position'  => $category->getPosition(),
            'name'      => $category->getName(),
            'url_path'  => $category->getRequestPath(),
        ]);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [
            Menu::CACHE_TAG . '_' . $this->getMenuId()
        ];
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ITEM_ID);
    }

    /**
     * Get Menu ID
     *
     * @return int
     */
    public function getMenuId()
    {
        return $this->getData(self::MENU_ID);
    }

    /**
     * Get Store Id
     *
     * @return integer
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * Get is_active
     *
     * @return int
     */
    public function getIsActive()
    {
        return (int) $this->getData(self::IS_ACTIVE);
    }

    /**
     * Set ID
     *
     * @param  int $id
     * @return ItemInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ITEM_ID, $id);
    }

    /**
     * Set Menu ID
     *
     * @param  int $id
     * @return ItemInterface
     */
    public function setMenuId($id)
    {
        return $this->setData(self::MENU_ID, $id);
    }

    /**
     * Set store Id
     *
     * @param integer $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * Set item name
     *
     * @param  int $name
     * @return ItemInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }


    /**
     * Set is_active
     *
     * @param  int $isActive
     * @return ItemInterface
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }
}
