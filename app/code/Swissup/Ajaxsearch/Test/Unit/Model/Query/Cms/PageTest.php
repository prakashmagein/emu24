<?php

namespace Swissup\Ajaxsearch\Test\Unit\Model\Query\Cms;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

// use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class PageTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Cms\Model\ResourceModel\Page\Grid\Collection|\PHPUnit\Framework\MockObject\MockObject
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
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool|\PHPUnit\Framework\MockObject\MockObject
     */
    private $metadataPoolMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManagerHelper($this);

        $this->resourceMock = $this->getMockBuilder(\Magento\Search\Model\ResourceModel\Query::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionMock = $this->getMockBuilder(
            \Magento\Cms\Model\ResourceModel\Page\Grid\Collection::class
        )
            ->setMethods([
                'getIterator',
                'setPageSize',
                'addStoreFilter',
                'addFieldToFilter',
                'getSelect',
                'getTable'
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

        $queryString = 'contact';

        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->setMethods(['getStore'])
            ->getMockForAbstractClass();

        $this->metadataPoolMock = $this->createMock(\Magento\Framework\EntityManager\MetadataPool::class);

        $this->model = $objectManager->getObject(
            \Swissup\Ajaxsearch\Model\Query\Cms\Page::class,
            [
                'resource' => $this->resourceMock,
                'queryCollectionFactory' => $this->queryCollectionFactoryMock,
                'configHelper' => $this->configHelperMock,
                'filterPool' => $this->filterPoolMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'filterBuilder' => $this->filterBuilderMock,
                'metadataPool' => $this->metadataPoolMock,
                'data' => [
                    'query_text' => $queryString
                ],
                'storeManager' => $this->storeManagerMock
            ]
        );
    }

    public function testGetSuggestCollection()
    {
        $this->queryCollectionFactoryMock->expects($this->once())
            ->method('setInstanceName')
            ->with(\Magento\Cms\Model\ResourceModel\Page\Grid\Collection::class)
            ->willReturnSelf();

        $this->queryCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->collectionMock);

        $limit = 10;
        $this->configHelperMock->expects($this->once())
            ->method('getPageLimit')
            ->willReturn($limit);

        $this->collectionMock->expects($this->once())
            ->method('setPageSize')
            ->with($limit)
            ->willReturnSelf();

        $this->collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with('is_active', 1)
            ->willReturnSelf();

        $selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $this->collectionMock->expects($this->any())
            ->method('getSelect')
            ->willReturn($selectMock);

        $this->collectionMock->expects($this->once())
            ->method('getTable')
            ->with('cms_page_store')
            ->willReturn('cms_page_store');

        $entityMetadataMock = $this->createMock(\Magento\Framework\EntityManager\EntityMetadataInterface::class);

        $entityMetadataMock->expects($this->once())
            ->method('getLinkField')
            ->willReturn('');

        $this->metadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->with(\Magento\Cms\Api\Data\PageInterface::class)
            ->willReturn($entityMetadataMock)
        ;

        $storeId = 1;

        $storeMock = $this->getMockBuilder(
            \Magento\Store\Model\Store::class
        )->disableOriginalConstructor()->setMethods(
            ['getId']
        )->getMock();
        $storeMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($storeId));

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->collectionMock->expects($this->any())
            ->method('addStoreFilter')
            ->with($storeId)
            ->willReturnSelf();

        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->willReturnSelf();

        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($this->searchCriteriaMock);

        $this->searchCriteriaMock->expects($this->once())
            ->method('setRequestName')
            ->with('cms_page_listing_data_source')
            ->willReturnSelf();

        $this->filterBuilderMock->expects($this->once())
            ->method('setConditionType')
            ->with('fulltext')
            ->willReturnSelf();

        $this->filterBuilderMock->expects($this->once())
            ->method('setField')
            ->with('fulltext')
            ->willReturnSelf();

        $this->filterBuilderMock->expects($this->once())
            ->method('setValue')
            ->with('contact')
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
