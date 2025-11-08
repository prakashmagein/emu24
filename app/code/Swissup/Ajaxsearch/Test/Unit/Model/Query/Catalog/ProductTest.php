<?php

namespace Swissup\Ajaxsearch\Test\Unit\Model\Query\Catalog;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Swissup\Ajaxsearch\Model\Query|\Magento\Search\Model\ResourceModel\Query|\PHPUnit\Framework\MockObject\MockObject
     */
    private $model;

    /** @var ObjectManagerHelper */
    private $objectManagerHelper;

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
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $searchLayerFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $toolbarMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManagerHelper($this);

        $this->collectionMock = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Category\Collection::class
        )
            ->setMethods([
                'setCurPage',
                'setPageSize',
                'setOrder',
                'load',
                // 'addSearchFilter'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->configHelperMock = $this->getMockBuilder(\Swissup\Ajaxsearch\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchLayerFactoryMock =  $this
            ->getMockBuilder(\Swissup\Ajaxsearch\Model\Layer\SearchFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->toolbarMock = $this
            ->getMockBuilder(\Magento\Catalog\Block\Product\ProductList\Toolbar::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getAvailableOrders',
                'setAvailableOrders',
                'setDefaultOrder',
                'setDefaultDirection',
                'getCurrentOrder',
                'getCurrentDirection',
            ])
            ->getMock();

        $this->toolbarMock->expects($this->any())
            ->method('getAvailableOrders')
            ->willReturn([]);

        $availableOrders = ['relevance' => 'Relevance'];
        $this->toolbarMock->expects($this->once())
            ->method('setAvailableOrders')
            ->with($availableOrders)
            ->willReturnSelf();
        $this->toolbarMock->expects($this->once())
            ->method('setDefaultDirection')
            ->willReturnSelf();
        $this->toolbarMock->expects($this->once())
            ->method('setDefaultOrder')
            ->willReturnSelf();
        // $queryString = $this->getQueryText();

        $this->model = $objectManager->getObject(
            \Swissup\Ajaxsearch\Model\Query\Catalog\Product::class,
            [
                'configHelper' => $this->configHelperMock,
                'searchLayerFactory' => $this->searchLayerFactoryMock,
                'toolbar' => $this->toolbarMock
                // 'data' => [
                //     'query_text' => $queryString,
                // ]
            ]
        );
    }

    /**
     *
     * @return string
     */
    private function getQueryText()
    {
        return 'Celeste';
    }

    public function testGetSuggestCollection()
    {
        $layerMock = $this
            ->getMockBuilder(\Swissup\Ajaxsearch\Model\Layer\Search::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProductCollection', 'getCurrentCategory'])
            ->getMock();

        $layerMock->expects($this->once())
            ->method('getProductCollection')
            ->willReturn($this->collectionMock);

        $this->toolbarMock->expects($this->any())
            ->method('getCurrentOrder')
            ->willReturn('relevance');

        $this->toolbarMock->expects($this->once())
            ->method('getCurrentDirection')
            ->willReturn('asc');

        $limit = 10;
        $this->configHelperMock->expects($this->once())
            ->method('getProductLimit')
            ->willReturn($limit);

        $this->collectionMock->expects($this->once())
            ->method('setCurPage')
            ->with(1)
            ->willReturnSelf();

        $this->collectionMock->expects($this->once())
            ->method('setPageSize')
            ->with($limit)
            ->willReturnSelf();

        $this->collectionMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();

        $this->collectionMock->expects($this->at(2))
            ->method('setOrder')
            ->with('relevance', 'asc')
            ->willReturnSelf();

//        $this->collectionMock->expects($this->at(1))
//            ->method('setOrder')
//            ->with('entity_id')
//            ->willReturnSelf();

        $layerMock->expects($this->once())
            ->method('getProductCollection')
            ->willReturn($this->collectionMock);

        $this->searchLayerFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($layerMock);

        $collection = $this->model->getSuggestCollection();
        // $this->assertEquals([], $result);
    }
}
