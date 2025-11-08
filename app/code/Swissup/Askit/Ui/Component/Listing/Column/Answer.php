<?php

namespace Swissup\Askit\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Filter\FilterManager;

class Answer extends Column
{
    /**
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * @param FilterManager      $filterManager
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        FilterManager $filterManager,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->filterManager = $filterManager;
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
            $item[$this->getData('name')] = $this->prepareItem($item);
        }

        return $dataSource;
    }

    /**
     * @param  array  $item
     * @return string
     */
    protected function prepareItem(array $item)
    {
        $text = $this->filterManager->stripTags(
            $item[$this->getData('name')],
            ['allowableTags' => false, 'escape' => null]
        );
        $text = $this->filterManager->truncate(
            $text,
            ['length' => 200, 'breakWords' => false, 'etc' => '...']
        );

        return $text;
    }
}
