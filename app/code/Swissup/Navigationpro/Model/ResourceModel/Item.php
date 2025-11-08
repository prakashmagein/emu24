<?php

namespace Swissup\Navigationpro\Model\ResourceModel;

use Magento\Store\Model\Store;
use Magento\Framework\Exception\LocalizedException;

class Item extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Swissup\Navigationpro\Model\MenuRepository
     */
    protected $menuRepository;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Swissup\Navigationpro\Model\MenuRepository $menuRepository
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Swissup\Navigationpro\Model\MenuRepository $menuRepository,
        $connectionName = null
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->menuRepository = $menuRepository;
        parent::__construct($context, $connectionName);
    }

    protected function _construct()
    {
        $this->_init('swissup_navigationpro_item', 'item_id');
    }

    /**
     * 1. Prepare dropdown_settings object
     * 2. Load scope-specific value to use together with "Use Default Value" checkboxes
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(['content' => $this->getTable('swissup_navigationpro_item_content')])
            ->where('content.item_id = ?', $object->getId())
            ->where('content.store_id IN (?)', [$object->getStoreId(), Store::DEFAULT_STORE_ID]);

        $result = $connection->fetchAll($select);
        if (!$result) {
            return;
        }

        $assocData = [
            'content' => [
                'default' => [],
                'scope' => [],
            ]
        ];
        foreach ($result as $data) {
            if ($data['dropdown_settings']) {
                $data['dropdown_settings'] = $this->jsonHelper->jsonDecode(
                    $data['dropdown_settings']
                );
            } else {
                $data['dropdown_settings'] = [
                    'use_menu_settings' => '1'
                ];
            }

            if ($data['store_id'] == Store::DEFAULT_STORE_ID) {
                $assocData['content']['default'] = $data;
            } else {
                $assocData['content']['scope'] = $data;
            }
        }

        $object->addData($assocData);
        $object->addContentData(
            $assocData['content']['default'],
            $assocData['content']['scope']
        );

        return parent::_afterLoad($object);
    }

    /**
     * 1. Prepare dropdown_settings column
     * 2. Prepare parent item dependent properties
     * 3. Update item position and place it into the right place
     *
     * @param  \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $dropdownSettings = $object->getDropdownSettings();
        if (is_array($dropdownSettings)) {
            $dropdownSettings = $this->jsonHelper->jsonEncode($dropdownSettings);
            $dropdownSettings = str_replace('\\', '', $dropdownSettings);
        }

        $this->menuRepository->getById($object->getMenuId())->validateRecursiveCalls([
            'Dropdown Settings' => $dropdownSettings,
            'Name as Html' => $object->getHtml(),
        ]);

        if ($object->isObjectNew()
            || $object->getParentId() !== $object->getOrigData('parent_id')
            || $object->getForceParentUpdate()
        ) {
            // Prevent recursion:
            //
            // Child path:
            // 0/3
            //
            // Possible parents paths and expected results:
            // 0        allow
            // 0/33     allow
            // 0/3/4    disallow
            // 0/3      disallow
            //
            $parentPath = $object->getParentItem()->getPath() . '/';
            $itemPath   = $object->getPath() . '/';
            if (strpos($parentPath, $itemPath) === 0) {
                throw new LocalizedException(
                    __('An item cannot be parent for itself')
                );
            }

            $object->setLevel($object->getParentItem()->getLevel() + 1);
            $object->setPath($object->getParentItem()->getPath() . '/' . (string)$object->getId());
        }

        $position = $object->getPosition();
        if ($object->getInsertBefore()) {
            // move item above next_sibling_id
            // and increment position of all next items
            $position = $object->getInsertBefore()->getPosition();

            // @todo: move to single query method
            $nextSiblings = $object->getInsertBefore()->getNextSiblingItems();
            foreach ($nextSiblings as $sibling) {
                if ($sibling->getId() === $object->getId()) {
                    continue;
                }

                $sibling
                    ->setSkipContentUpdate(true)
                    ->setPosition($sibling->getPosition() + 1)
                    ->save();
            }
            $object->getInsertBefore()
                ->setPosition($position + 1)
                ->save();
        } elseif ($position === null) {
            // move newly created item to the bottom
            $maxPosition = $object->getParentItem()
                ->getLastChildItem()
                ->getPosition();
            $position = $maxPosition + 1;
        }
        $object->setPosition($position);

        return $this;
    }

    /**
     * Perform actions after object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (substr($object->getPath(), -1) === '/') {
            $object->setPath($object->getPath() . $object->getId());
            $this->savePath($object);
        }

        if (!$object->getSkipContentUpdate()) {
            $this->saveContent($object);
        }

        if (!$object->isObjectNew()
            && ($object->getParentId() !== $object->getOrigData('parent_id')
                || $object->getForceParentUpdate())
        ) {
            foreach ($object->getChildrenItems() as $item) {
                $item
                    ->setForceParentUpdate(true)
                    ->setSkipContentUpdate(true)
                    ->save();
            }
        }

        return $this;
    }

    /**
     * Save content fields
     *
     * @param  \Swissup\Navigationpro\Model\Item $object
     * @return $this
     */
    protected function saveContent($object)
    {
        $connection = $this->getConnection();
        $table = $this->getTable('swissup_navigationpro_item_content');

        $where = [
            'item_id = ?' => (int)$object->getId(),
            'store_id = ?' => (int)$object->getStoreId(),
        ];
        $connection->delete($table, $where);

        $data = array_fill_keys([
            'item_id',
            'store_id',
            'name',
            'url_path',
            'html',
            'css_class',
            'dropdown_settings',
        ], null);

        $data['store_id'] = (int)$object->getStoreId();

        foreach ($data as $key => $value) {
            $value = $object->getData($key);
            if ($key === 'store_id' && null === $value) {
                continue;
            }

            $data[$key] = $value;

            if (in_array($key, ['dropdown_settings']) && is_array($data[$key])) {
                $data[$key] = $this->jsonHelper->jsonEncode($data[$key]);
            }
        }

        $connection->insert($table, $data);

        return $this;
    }

    /**
     * Update path and level fields
     *
     * @param \Swissup\Navigationpro\Model\Item $object
     * @return $this
     */
    protected function savePath($object)
    {
        if ($object->getId()) {
            $this->getConnection()->update(
                $this->getMainTable(),
                ['path' => $object->getPath()],
                ['item_id = ?' => $object->getId()]
            );
        }
        return $this;
    }
}
