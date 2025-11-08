<?php

namespace Swissup\Easybanner\Model\Layout;

class Builder
{
    /**
     * @var \Swissup\Easybanner\Model\ResourceModel\Placeholder\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\Math\Random
     */
    private $mathRandom;

    public function __construct(
        \Swissup\Easybanner\Model\ResourceModel\Placeholder\CollectionFactory $collectionFactory,
        \Magento\Framework\Math\Random $mathRandom
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->mathRandom = $mathRandom;
    }

    /**
     * Generate layout updates with placeholders
     *
     * @param \Magento\Framework\View\Model\Layout\Merge $subject
     * @param $result
     * @param $handle
     * @return  string
     */
    public function generateLayoutUpdate()
    {
        $placeholders = $this->collectionFactory->create()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('container', ['neq' => '']);

        if (!$placeholders->count()) {
            return '';
        }

        $argumentsMapping = [
            'name' => 'placeholder',
            'additional_css_class' => 'additional_css_class',
        ];
        $xml = '<body>';
        foreach ($placeholders as $placeholder) {
            $hash = $this->mathRandom->getUniqueHash();
            $xml .= '<referenceContainer name="' . $placeholder->getContainer() . '">';
            $xml .= '<block class="Swissup\Easybanner\Block\Placeholder" '
                    . 'name="' . $hash . '" '
                    . (string)$placeholder->getPosition() . '>';
            $xml .= '<arguments>';

            foreach ($argumentsMapping as $modelKey => $argumentName) {
                if (!$placeholder->hasData($modelKey)) {
                    continue;
                }

                $xml .= '<argument name="' . $argumentName . '" xsi:type="string"><![CDATA['
                        . $placeholder->getData($modelKey)
                        . ']]></argument>';
            }

            $xml .= '</arguments>';
            $xml .= '</block>';
            $xml .= '</referenceContainer>';
        }
        $xml .= '</body>';

        return $xml;
    }
}
