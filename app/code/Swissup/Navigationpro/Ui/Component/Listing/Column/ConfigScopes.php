<?php

namespace Swissup\Navigationpro\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class ConfigScopes extends Column
{
    /**
     * System store
     *
     * @var SystemStore
     */
    protected $systemStore;

    /**
     * Constructor
     *
     * @param SystemStore $systemStore
     * @param Escaper $escaper
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SystemStore $systemStore,
        array $components = [],
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            if (empty($item[$this->getData('name')])) {
                $item[$this->getData('name')] = '-';
                continue;
            }

            $result = [];
            foreach ($item[$this->getData('name')] as $scopeId) {
                if (!$scopeId) {
                    $result[] = __('All Store Views');
                } elseif (strpos($scopeId, 'website_') === 0) {
                    $scopeId = str_replace('website_', '', $scopeId);
                    $result[] = $this->systemStore->getWebsiteName($scopeId);
                } else {
                    $result[] = $this->systemStore->getStoreName($scopeId);
                }
            }
            $item[$this->getData('name')] = implode(', ', $result);
        }

        return $dataSource;
    }
}
