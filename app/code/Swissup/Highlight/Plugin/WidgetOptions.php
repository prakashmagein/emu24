<?php

namespace Swissup\Highlight\Plugin;

use Magento\Framework\DataObject;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Widget\Model\Widget;

class WidgetOptions
{
    private ModuleManager $moduleManager;

    public function __construct(ModuleManager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    public function afterGetConfigAsObject(Widget $subject, DataObject $result, $type)
    {
        if (strpos($type, 'Swissup\Highlight') !== false &&
            $this->moduleManager->isOutputEnabled('Smile_ElasticsuiteCatalog')
        ) {
            $params = $result->getParameters();
            unset($params['condition']);
            $result->setParameters($params);
        }
        return $result;
    }
}
