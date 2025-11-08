<?php
declare(strict_types=1);

namespace Swissup\Ajaxsearch\Model\Resolver\Items\Query;

use Magento\Search\Model\QueryFactory;
use Swissup\Ajaxsearch\Model\Resolver\Items\SearchResult;
use Swissup\Ajaxsearch\Model\Resolver\Items\SearchResultFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Search\Model\AutocompleteInterface;
use Magento\Cms\Api\Data\PageInterface;

class Search implements ItemQueryInterface
{
    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * @var  \Magento\Search\Model\AutocompleteInterface
     */
    private $autocomplete;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $httpRequest;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    /**
     * @param SearchResultFactory $searchResultFactory
     * @param \Magento\Search\Model\AutocompleteInterface $autocomplete
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     */
    public function __construct(
        SearchResultFactory $searchResultFactory,
        AutocompleteInterface $autocomplete,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\State $appState,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        $this->searchResultFactory = $searchResultFactory;
        $this->autocomplete = $autocomplete;
        /** @var \Magento\Framework\App\Request\Http $request */
        $this->httpRequest = $request;
        $this->appState = $appState;
        $this->productRepository = $productRepository;
    }

    /**
     * Return product search results using Search API
     *
     * @param array $args
     * @param ResolveInfo $info
     * @param ContextInterface $context
     * @return SearchResult
     * @throws InputException
     */
    public function getResult(
        array $args,
        ResolveInfo $info,
        ContextInterface $context
    ): SearchResult {

        $pageSize = $args['pageSize'];
        $currentPage = $args['currentPage'];

        if (isset($args['search'])) {
            $queryParam = \Magento\Search\Model\QueryFactory::QUERY_VAR_NAME;
            $searchValue = urldecode($args['search']);
            $this->httpRequest->setPostValue($queryParam, $searchValue);
        }

        if (isset($args['category'])) {
            $categoryParam = \Swissup\Ajaxsearch\Model\QueryFactory::CATEGORY_VAR_NAME;
            $categoryValue = $args['category'];
            $this->httpRequest->setPostValue($categoryParam, $categoryValue);
        }

        $autocompleteData = [];
        $autocomplete = $this->autocomplete;
        $this->appState->emulateAreaCode(
            \Magento\Framework\App\Area::AREA_FRONTEND,
            function () use ($autocomplete, &$autocompleteData) {
                $autocompleteData = $autocomplete->getItems();
            }
        );

        $totalCount = count($autocompleteData);
        $totalPages = (int) ceil($totalCount / $pageSize);

        $realCurrentPage = $currentPage - 1;
        $autocompleteData = array_slice($autocompleteData, $realCurrentPage * $pageSize, $pageSize);

        $autocompletesArray = $productsArray = $categoriesArray = $pagesArray = [];
        foreach ($autocompleteData as $autocompleteDataItem) {
            /** @var \Magento\Search\Model\Autocomplete\Item $autocompleteDataItem */
            $data = $autocompleteDataItem->getData();
            $type = isset($data['_type']) ? $data['_type'] : 'autocomplete';

            switch ($type) {
                case 'product':
                    $productsArray[] = $this->prepareProductData($data);
                    break;
                case 'category':
                    $categoriesArray[] = $this->prepareCategoryData($data);
                    break;
                case 'page':
                    $pagesArray[] = $this->preparePageData($data);
                    break;
                case 'autocomplete':
                default:
                    $autocompletesArray[] = $this->prepareAutompleteData($data);
                    break;
            }
        }

        $suggestions = [
            'autocompletes' => $autocompletesArray,
            'products' => $productsArray,
            'categories' => $categoriesArray,
            'pages' => $pagesArray,
        ];

        return $this->searchResultFactory->create(
            [
                'totalCount' => $totalCount,
                'suggestionsSearchResult' => $suggestions,
                //'searchAggregation' => $itemsResults->getAggregations(),
                'pageSize' => $pageSize,
                'currentPage' => $currentPage,
                'totalPages' => $totalPages,
            ]
        );
    }

    /**
     * @param array $data
     * @return array
     */
    private function prepareProductData(array $data): array
    {
        $result = [];
        $productId = isset($data['id']) ? $data['id'] : $data['entity_id'];
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->getById($productId);
        $result = array_merge($product->getData(), $data);
        $result['ajaxsearch_result'] = $data;
        $result['model'] = $product;

        return $result;
    }

    /**
     * @param array $data
     * @return array
     */
    private function prepareAutompleteData(array $data): array
    {
        $result = [];
        foreach (['title', 'num_results'] as $key) {
            $result[$key] = $data[$key];
            unset($data[$key]);
        }

        return $result;
    }

    /**
     * @param array $data
     * @return array
     */
    private function prepareCategoryData(array $data): array
    {
        $result = [];
        $keys = [
            'title',
            'num_results',
            'url',
            'url_key' => 'request_path',
            //'url_suffix' //AjaxsearchCatalogUrlSuffixInterface
        ];
        foreach ($keys as $key => $valueKey) {
            if (!is_string($key)) {
                $key = $valueKey;
            }
            $result[$key] = isset($data[$valueKey]) ? $data[$valueKey] : null;
        }

        return $result;
    }

    /**
     * @param array $data
     * @return array
     */
    private function preparePageData(array $data): array
    {
        $result = [];
        $keys = [
            'title',
            'num_results',
            'url',
            'url_key' => PageInterface::IDENTIFIER
        ];
        foreach ($keys as $key => $valueKey) {
            if (!is_string($key)) {
                $key = $valueKey;
            }
            $result[$key] = isset($data[$valueKey]) ? $data[$valueKey] : null;
        }

        return $result;
    }
}
