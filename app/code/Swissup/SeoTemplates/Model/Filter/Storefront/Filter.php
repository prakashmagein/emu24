<?php

namespace Swissup\SeoTemplates\Model\Filter\Storefront;

class Filter extends \Swissup\SeoCore\Model\Filter\AbstractFilter
{
    private $request;
    private $attributeRepository;
    private $searchCriteriaBuilder;

    /**
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\App\RequestInterface $httpRequest
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\App\RequestInterface $httpRequest,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($filterManager);
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->request = $httpRequest;
    }

    /**
     * Translate directive at storefront
     *
     * @param  array $construction
     * @return string
     */
    public function i18nDirective($construction)
    {
        $getIncludeParameters = [$this, '_getIncludeParameters'];
        if (!$params = $getIncludeParameters($construction[2])) {
            $params = $getIncludeParameters(' text='.trim($construction[2]));
        }

        $text = $params['text'] ?? null;

        return $text ? __($text) : '';
    }

    public function activeFiltersDirective($construction)
    {
        $getIncludeParameters = [$this, '_getIncludeParameters'];
        $params = $getIncludeParameters($construction[2]);
        $limit = $params['limit'] ?? 20;
        $separator = $params['separator'] ?? '';
        $prefix = $params['prefix'] ?? '';
        $suffix = $params['suffix'] ?? '';
        $allowedAttributeCodes = $params['attribute'] ? explode(',', $params['attribute']) : [];

        $activatedFilters = $this->getActiveFilters($allowedAttributeCodes);
        $request = $this->request;

        $result = [];
        $limitCounter = 0;
        foreach ($activatedFilters as $filter) {
            $activatedOptionsValues = explode(',', $request->getParam($filter->getAttributeCode()));
            $optionsResult = [];
            foreach ($filter->getOptions() as $option) {
                if (in_array($option['value'], $activatedOptionsValues)) {
                    $optionsResult[$option['value']] = $option['label'];
                    $limitCounter++;
                }
            }
            $result[] = /*$filter->getStoreLabel()  . ':' . */ implode('&', $optionsResult);
            if ($limitCounter >= $limit) {
                break;
            }
        }

        return $prefix . implode($separator, $result) . $suffix;
    }

    private function getActiveFilters($allowedCodes = []): array
    {
        $ignore = ['id'];
        $params = $this->request->getParams();
        $activated = array_diff_key($params, array_flip($ignore));
        $activated = array_keys($activated);

        if (!empty($allowedCodes)) {
            $activated = array_intersect($allowedCodes, $activated);
        }

        if (empty($activated)) {
            return [];
        }

        $criteria = $this->searchCriteriaBuilder
            ->addFilter('attribute_code', implode(',', $activated), 'in')
            ->create();

        return $this->attributeRepository->getList($criteria)->getItems();
    }
}
