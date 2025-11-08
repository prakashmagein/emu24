<?php
namespace Swissup\ChatGptAssistant\Model;

class Prompt extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Prompt's statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Swissup\ChatGptAssistant\Model\ResourceModel\Prompt::class);
    }

    /**
     * Prepare prompt's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ENABLED => __('Enabled'),
            self::STATUS_DISABLED => __('Disabled')
        ];
    }

    /**
     * Prepare data before saving
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        if ($this->hasFieldIds()) {
            $fieldIds = $this->getFieldIds();
            if (is_array($fieldIds) && !empty($fieldIds)) {
                $this->setFieldIds(implode(',', $fieldIds));
            }
        }

        parent::beforeSave();
        return $this;
    }
}
