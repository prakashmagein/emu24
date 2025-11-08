<?php

namespace Swissup\Ajaxsearch\Test\Unit\Model\Query\Catalog;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class CategoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Swissup\Ajaxsearch\Model\Query|\Magento\Search\Model\ResourceModel\Query|\PHPUnit\Framework\MockObject\MockObject
     */
    private $model;

    /** @var ObjectManagerHelper */
    private $objectManagerHelper;

    /**
     * @var \Magento\Search\Model\ResourceModel\Query|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resourceMock;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Collection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $collectionMock;

    /**
     * @var \Swissup\Ajaxsearch\Model\Query\CollectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $queryCollectionFactoryMock;

    /**
     * @var \Swissup\Ajaxsearch\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configHelperMock;

    /**
     * @var \Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool|\PHPUnit\Framework\MockObject\MockObject
     */
    private $filterPoolMock;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $searchCriteriaMock;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteria|\PHPUnit\Framework\MockObject\MockObject
     */
    private $filterBuilderMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManagerHelper($this);

        $this->resourceMock = $this->getMockBuilder(\Magento\Search\Model\ResourceModel\Query::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionMock = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Category\Collection::class
        )
            ->setMethods([
                'setStoreId',
                'addIsActiveFilter',
                'addNameToResult',
                'joinUrlRewrite',
                'addOrderField',
                'setPageSize',
            ])
            ->disableOriginalConstructor()
            ->getMock();

        /* \Swissup\Ajaxsearch\Model\Query\CollectionFactory::class */
        $this->queryCollectionFactoryMock =  $this
            ->getMockBuilder(\Magento\Search\Model\ResourceModel\Query\CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'setInstanceName'])
            ->getMock();

        $this->queryCollectionFactoryMock =  $this
            ->getMockBuilder(\Swissup\Ajaxsearch\Model\Query\CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'setInstanceName'])
            ->getMock();

        $this->configHelperMock = $this->getMockBuilder(\Swissup\Ajaxsearch\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterPoolMock = $this
            ->getMockBuilder(\Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool::class)
            ->disableOriginalConstructor()
            ->setMethods(['applyFilters'])
            ->getMock();

        $this->searchCriteriaBuilderMock = $this
            ->getMockBuilder(\Magento\Framework\Api\Search\SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'addFilter'])
            ->getMock();

        $this->searchCriteriaMock = $this
            ->getMockBuilder(\Magento\Framework\Api\Search\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRequestName'])
            ->getMock();

        $this->filterBuilderMock = $this
            ->getMockBuilder(\Magento\Framework\Api\FilterBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['setConditionType', 'setField', 'setValue', 'create'])
            ->getMock();

        $queryString = $this->getQueryText();
        $storeId = $this->getStoreId();

        $this->model = $objectManager->getObject(
            \Swissup\Ajaxsearch\Model\Query\Catalog\Category::class,
            [
                'resource' => $this->resourceMock,
                'queryCollectionFactory' => $this->queryCollectionFactoryMock,
                'configHelper' => $this->configHelperMock,
                'filterPool' => $this->filterPoolMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'filterBuilder' => $this->filterBuilderMock,
                'data' => [
                    'query_text' => $queryString,
                    'store_id' => $storeId
                ]
            ]
        );
    }

    /**
     *
     * @return string
     */
    private function getQueryText()
    {
        return 'contact';
    }

    /**
     *
     * @return int
     */
    private function getStoreId()
    {
        return 1;
    }

    public function testGetSuggestCollection()
    {
        $this->queryCollectionFactoryMock->expects($this->once())
            ->method('setInstanceName')
            ->with(\Magento\Catalog\Model\ResourceModel\Category\Collection::class)
            ->willReturnSelf();

        $this->queryCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->collectionMock);

        $storeId = $this->getStoreId();
        $this->collectionMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();

        $this->collectionMock->expects($this->once())
            ->method('addIsActiveFilter')
            ->willReturnSelf();

        $this->collectionMock->expects($this->once())
            ->method('addNameToResult')
            ->willReturnSelf();

        $this->collectionMock->expects($this->once())
            ->method('joinUrlRewrite')
            ->willReturnSelf();

        $this->collectionMock->expects($this->once())
            ->method('addOrderField')
            ->with('path')
            ->willReturnSelf();

        $limit = 10;
        $this->configHelperMock->expects($this->once())
            ->method('getCategoryLimit')
            ->willReturn($limit);

        $this->collectionMock->expects($this->once())
            ->method('setPageSize')
            ->with($limit)
            ->willReturnSelf();

        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->willReturnSelf();

        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($this->searchCriteriaMock);

        $this->searchCriteriaMock->expects($this->once())
            ->method('setRequestName')
            ->with('catalog_category_listing_data_source')
            ->willReturnSelf();

        $this->filterBuilderMock->expects($this->once())
            ->method('setConditionType')
            ->with('like')
            ->willReturnSelf();

        $this->filterBuilderMock->expects($this->once())
            ->method('setField')
            ->with('name')
            ->willReturnSelf();

        $queryString = $this->getQueryText();
        $this->filterBuilderMock->expects($this->once())
            ->method('setValue')
            ->with('%' . $queryString . '%')
            ->willReturnSelf();

        $filterMock = $this
            ->getMockBuilder(\Magento\Framework\Api\Filter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($filterMock);

        $this->filterPoolMock->expects($this->once())
            ->method('applyFilters')
            ;

        $collection = $this->model->getSuggestCollection();
        // $this->assertEquals([], $result);
    }
}
