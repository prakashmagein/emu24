<?php

namespace Swissup\Ajaxsearch\Test\Unit\Model\Catalog\Product;

use Magento\Search\Model\QueryFactory;
use Magento\Search\Model\Autocomplete\ItemFactory;
use Magento\CatalogSearch\Model\Autocomplete\DataProvider;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

use Swissup\Ajaxsearch\Helper\Data as ConfigHelper;

class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DataProvider|\Swissup\Ajaxsearch\Model\Catalog\Product\DataProvider
     */
    private $model;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Search\Model\Query|\PHPUnit\Framework\MockObject\MockObject
     */
    private $query;

    /**
     * @var \Swissup\Ajaxsearch\Model\QueryFactory |\PHPUnit\Framework\MockObject\MockObject
     */
    private $queryFactoryMock;

    /**
     * @var \Magento\Search\Model\Autocomplete\ItemFactory |\PHPUnit\Framework\MockObject\MockObject
     */
    private $itemFactoryMock;

    /**
     * @var \Magento\Search\Model\ResourceModel\Query\Collection |\PHPUnit\Framework\MockObject\MockObject
     */
    private $suggestCollection;

    /**
     * @var \Swissup\Ajaxsearch\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configHelperMock;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Collection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $collectionMock;

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $imageBuilderMock;

    protected function setUp(): void
    {
        $this->collectionMock = $this->getMockBuilder(
            \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection::class
        )
            ->setMethods([
                'getIterator',
                'setPageSize'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->query = $this->getMockBuilder(\Swissup\Ajaxsearch\Model\Query\Catalog\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQueryText', 'getSuggestCollection'])
            ->getMock();

        $this->query->expects($this->any())
            ->method('getSuggestCollection')
            ->willReturn($this->collectionMock);

        $this->queryFactoryMock = $this->getMockBuilder(\Swissup\Ajaxsearch\Model\QueryFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $this->queryFactoryMock->expects($this->any())
            ->method('get')
            ->willReturn($this->query);

        $this->itemFactoryMock = $this->getMockBuilder(\Magento\Search\Model\Autocomplete\ItemFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->configHelperMock = $this->getMockBuilder(\Swissup\Ajaxsearch\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->imageBuilderMock = $this->getMockBuilder(\Magento\Catalog\Block\Product\ImageBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['setProduct', 'setImageId', 'setAttributes', 'create', 'setTemplate'])
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Swissup\Ajaxsearch\Model\Catalog\Product\DataProvider::class,
            [
                'queryFactory' => $this->queryFactoryMock,
                'itemFactory' => $this->itemFactoryMock,
                'configHelper' => $this->configHelperMock,
                'imageBuilder' => $this->imageBuilderMock,
            ]
        );
    }

    public function testGetItemsDisable()
    {
        $this->configHelperMock->expects($this->once())
            ->method('isProductEnabled')
            ->willReturn(false);

        $result = $this->model->getItems();

        $this->assertEquals([], $result);
    }

    public function testGetItems()
    {
        $queryString = 'product';
        $expected = [
            '_type' => 'product',
            'title' => $queryString,
            'num_results' => '',
            'image' => 'product_page_image_small.gif',
            'url' => '/' . $queryString,
            'final_price' => 1
        ];
        $limit = 1;

        $this->configHelperMock->expects($this->once())
            ->method('isProductEnabled')
            ->willReturn(true);

        $collection = [
            ['name' => 'product1', 'url' => '/product1'],
            ['name' => 'product2', 'url' => '/product2'],
            ['name' => 'product3', 'url' => '/product3'],
            ['name' => $queryString, 'url' => '/' . $queryString]
        ];
        $collectionData = [];
        foreach ($collection as $collectionItem) {
            $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
                ->disableOriginalConstructor()
                ->setMethods(['getData','getName', 'getProductUrl', 'getFinalPrice', 'getPriceInfo'])
                ->getMock();
            $productMock->expects($this->once())
                ->method('getData')
                ->willReturn($collectionItem);
            $productMock->expects($this->once())
                ->method('getName')
                ->willReturn($collectionItem['name']);
            $productMock->expects($this->once())
                ->method('getProductUrl')
                ->willReturn($collectionItem['url']);
            $productMock->expects($this->any())
                ->method('getFinalPrice')
                ->willReturn(1);

            $priceInterfaceMock = $this->createMock(\Magento\Framework\Pricing\Price\PriceInterface::class);
            $priceInterfaceMock->expects($this->once())
                ->method('getValue')
                ->willReturn(1);

            $priceInfoMock = $this->createMock(\Magento\Framework\Pricing\PriceInfo\Base::class);
            $priceInfoMock->expects($this->once())
                ->method('getPrice')
                ->with('final_price')
                ->willReturn($priceInterfaceMock);

            $productMock->expects($this->once())
                ->method('getPriceInfo')
                ->willReturn($priceInfoMock);

            $collectionData[] = $productMock;
        }
        $this->collectionMock->expects($this->any())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator($collectionData)));

        $this->query->expects($this->any())
            ->method('getQueryText')
            ->willReturn($queryString);

        $itemMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTitle', 'toArray', 'getData'])
            ->getMock();

        $itemMock->expects($this->any())
            ->method('getData')
            ->willReturn([]);

        $itemMock->expects($this->any())
            ->method('getTitle')
            ->will($this->onConsecutiveCalls(
                $queryString,
                'product1',
                'product2',
                'product3'
            ));

        $itemMock->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue($expected));

        $this->itemFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($itemMock);

        $imageId = 'product_page_image_small';
        $attributes = [];

        $imageMock = $this->getMockBuilder(\Magento\Catalog\Block\Product\Image::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->imageBuilderMock->expects($this->any())
            ->method('setProduct')
            ->with($productMock)
            ->willReturnSelf();
        $this->imageBuilderMock->expects($this->any())
            ->method('setImageId')
            ->with($imageId)
            ->willReturnSelf();
        $this->imageBuilderMock->expects($this->any())
            ->method('setAttributes')
            ->with($attributes)
            ->willReturnSelf();
        $this->imageBuilderMock->expects($this->any())
            ->method('create')
            ->willReturn($imageMock);
        $imageMock->expects($this->any())
            ->method('setTemplate')
            ->willReturnSelf();
        $imageMock->expects($this->any())
            ->method('toHtml')
            ->willReturn('1$');

        $result = $this->model->getItems();
        $this->assertEquals($expected, $result[0]->toArray());
    }
}
