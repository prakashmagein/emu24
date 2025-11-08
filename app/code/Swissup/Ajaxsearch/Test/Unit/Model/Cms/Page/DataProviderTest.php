<?php

namespace Swissup\Ajaxsearch\Test\Unit\Model\Cms\Page;

use Magento\Search\Model\QueryFactory;
use Magento\Search\Model\Autocomplete\ItemFactory;
use Magento\CatalogSearch\Model\Autocomplete\DataProvider;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

use Swissup\Ajaxsearch\Helper\Data as ConfigHelper;

class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DataProvider|\Swissup\Ajaxsearch\Model\Cms\Page\DataProvider
     */
    private $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Search\Model\Query |\PHPUnit\Framework\MockObject\MockObject
     */
    private $query;

    /**
     * @var \Magento\Search\Model\Autocomplete\ItemFactory |\PHPUnit\Framework\MockObject\MockObject
     */
    private $itemFactoryMock;

    /**
     * @var \Swissup\Ajaxsearch\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configHelperMock;

    /**
     * @var \Magento\Cms\Model\ResourceModel\Page\Grid\Collection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $collection;

    /**
     * @var \Magento\Cms\Helper\Page|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cmsPageHelperMock;

    protected function setUp(): void
    {

        $this->query = $this->getMockBuilder(\Swissup\Ajaxsearch\Model\Query::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQueryText', 'getSuggestCollection'])
            ->getMock();

        $queryFactory = $this->getMockBuilder(\Swissup\Ajaxsearch\Model\QueryFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $queryFactory->expects($this->any())
            ->method('get')
            ->willReturn($this->query);

        $this->itemFactoryMock = $this->getMockBuilder(\Magento\Search\Model\Autocomplete\ItemFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->configHelperMock = $this->getMockBuilder(\Swissup\Ajaxsearch\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collection = $this->getMockBuilder(
            \Magento\Cms\Model\ResourceModel\Page\Grid\Collection::class
        )
            ->setMethods([
                'getIterator',
                'setPageSize'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->query->expects($this->any())
            ->method('getSuggestCollection')
            ->willReturn($this->collection);

        $this->cmsPageHelperMock = $this->getMockBuilder(\Magento\Cms\Helper\Page::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Swissup\Ajaxsearch\Model\Cms\Page\DataProvider::class,
            [
                'queryFactory' => $queryFactory,
                'itemFactory' => $this->itemFactoryMock,
                'configHelper' => $this->configHelperMock,
                'cmsPageHelper' => $this->cmsPageHelperMock,
            ]
        );
    }

    public function testGetItemsDisable()
    {
        $this->configHelperMock->expects($this->once())
            ->method('isPageEnabled')
            ->willReturn(false);

        $result = $this->model->getItems();

        $this->assertEquals([], $result);
    }

    public function testGetItems()
    {
        $queryString = 'page';
        $expected = [
            '_type' => 'page',
            'title' => $queryString,
            'num_results' => '',
            'url' => '/' . $queryString
        ];
        $limit = 1;

        $this->configHelperMock->expects($this->once())
            ->method('isPageEnabled')
            ->willReturn(true);

        $collection = [
            ['title' => 'page1', 'url' => '/page1'],
            ['title' => 'page2', 'url' => '/page2'],
            ['title' => $queryString, 'url' => '/' . $queryString]
        ];
        $collectionData = [];
        foreach ($collection as $collectionItem) {
            $collectionData[] = new \Magento\Framework\DataObject($collectionItem);
        }
        $this->collection->expects($this->any())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator($collectionData)));

        $this->query->expects($this->any())
            ->method('getQueryText')
            ->willReturn($queryString);

        $itemMock = $this->getMockBuilder(\Magento\Search\Model\Autocomplete\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTitle', 'toArray'])
            ->getMock();

        $itemMock->expects($this->any())
            ->method('getTitle')
            ->will($this->onConsecutiveCalls(
                $queryString,
                'page1',
                'page2'
            ));
        $itemMock->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue($expected));

        $this->itemFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($itemMock);

        $this->cmsPageHelperMock->expects($this->any())
            ->method('getPageUrl')
            ->will($this->onConsecutiveCalls(
                '/'. $queryString,
                '/page1',
                '/page2'
            ));

        $result = $this->model->getItems();
        $this->assertEquals(count($collection), count($result));
        $this->assertEquals($expected, $result[0]->toArray());
    }
}
