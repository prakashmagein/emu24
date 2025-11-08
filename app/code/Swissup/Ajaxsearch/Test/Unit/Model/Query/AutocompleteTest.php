<?php

namespace Swissup\Ajaxsearch\Test\Unit\Model\Query;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class AutocompleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Swissup\Ajaxsearch\Model\Query\Autocomplete
     */
    private $model;

    /** @var ObjectManagerHelper */
    private $objectManagerHelper;

    /**
     * @var \Magento\Search\Model\ResourceModel\Query|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resourceMock;

    /**
     * @var \Magento\Search\Model\ResourceModel\Query\Collection|\PHPUnit\Framework\MockObject\MockObject
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

    protected function setUp(): void
    {
        $objectManager = new ObjectManagerHelper($this);

        $this->collectionMock = $this->getMockBuilder(
            \Magento\Search\Model\ResourceModel\Query\Collection::class
        )
            ->setMethods([
                // 'getIterator',
                'setStoreId',
                'setQueryFilter',
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

        $queryString = 'tank';
        $storeId = 1;

        $this->model = $objectManager->getObject(
            \Swissup\Ajaxsearch\Model\Query\Autocomplete::class,
            [
                // 'resource' => $this->resourceMock,
                'queryCollectionFactory' => $this->queryCollectionFactoryMock,
                'configHelper' => $this->configHelperMock,
                'data' => [
                    'query_text' => $queryString,
                    'store_id' => $storeId
                ]
            ]
        );
    }

    public function testGetSuggestCollection()
    {
        $this->queryCollectionFactoryMock->expects($this->once())
            ->method('setInstanceName')
            ->with(\Magento\Search\Model\ResourceModel\Query\Collection::class)
            ->willReturnSelf();

        $this->queryCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->collectionMock);

        $this->collectionMock->expects($this->once())
            ->method('setStoreId')
            ->with(1)
            ->willReturnSelf();

        $this->collectionMock->expects($this->once())
            ->method('setQueryFilter')
            ->with('tank')
            ->willReturnSelf();

        $collection = $this->model->getSuggestCollection();
        // $this->assertEquals([], $result);
    }
}
