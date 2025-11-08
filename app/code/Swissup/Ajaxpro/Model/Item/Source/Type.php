<?php
namespace Swissup\Ajaxpro\Model\Item\Source;

class Type implements \Magento\Framework\Data\OptionSourceInterface
{

    protected function getTypes()
    {
        return [
            'popup' => 'Popup',
            'slide' => 'Slide',
        ];
    }
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        // $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->getTypes();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getTypes();
    }
}
