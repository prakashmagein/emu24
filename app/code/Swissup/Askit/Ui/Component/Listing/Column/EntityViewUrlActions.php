<?php
namespace Swissup\Askit\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Model\Product;
use Swissup\Askit\Api\Data\MessageInterface;
use Swissup\Askit\Helper\Url as UrlHelper;

class EntityViewUrlActions extends Column
{
    /**
     *
     * @var \Swissup\Askit\Helper\Url
     */
    protected $urlHelper;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     * @param \Swissup\Askit\Helper\Url $urlHelper
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlHelper $urlHelper,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlHelper = $urlHelper;
        $this->urlHelper->setUrlBuilder($urlBuilder);
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
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$row) {
                $name = $this->getData('name');
                if (isset($row['id']) && isset($row['item_type_id'])) {
                    $row[$name]['edit'] = $this->urlHelper->get(
                        $row['item_type_id'],
                        $row['item_id']
                    );
                }
            }
        }
        return $dataSource;
    }
}
