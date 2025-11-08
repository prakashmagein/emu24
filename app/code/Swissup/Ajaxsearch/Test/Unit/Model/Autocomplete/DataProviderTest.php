<?php

namespace Swissup\Ajaxsearch\Test\Unit\Model\Autocomplete;

use Magento\Search\Model\QueryFactory;
use Magento\Search\Model\Autocomplete\ItemFactory;

use Magento\CatalogSearch\Model\Autocomplete\DataProvider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

use Swissup\Ajaxsearch\Helper\Data as ConfigHelper;

class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DataProvider|\Swissup\Ajaxsearch\Model\Autocomplete\DataProvider
     */
    private $model;

    /**
     * @var \Magento\Search\Model\Query|\PHPUnit\Framework\MockObject\MockObject
     */
    private $query;

    /**
     * @var \Magento\Search\Model\Autocomplete\ItemFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $itemFactoryMock;

    /**
     * @var \Magento\Search\Model\ResourceModel\Query\Collection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $suggestCollection;

   /**
    * @var \Magento\Search\Model\QueryFactory|\PHPUnit\Framework\MockObject\MockObject
    */
    private $queryFactoryMock;

   /**
    * @var \Swissup\Ajaxsearch\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
    */
    private $configHelperMock;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->suggestCollection = $this->getMockBuilder(\Magento\Search\Model\ResourceModel\Query\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIterator', 'setPageSize'])
            ->getMock();

        $this->query = $this->getMockBuilder(\Swissup\Ajaxsearch\Model\Query\Autocomplete::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQueryText', 'getSuggestCollection'])
            ->getMock();
        $this->query->expects($this->any())
            ->method('getSuggestCollection')
            ->willReturn($this->suggestCollection);

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

        $this->model = $helper->getObject(
            \Swissup\Ajaxsearch\Model\Autocomplete\DataProvider::class,
            [
                'queryFactory' => $queryFactory,
                'itemFactory' => $this->itemFactoryMock,
                'configHelper' => $this->configHelperMock
            ]
        );
    }

    public function testGetItems()
    {
        $queryString = 'string';
        $limit = 1;
        $expected = ['title' => $queryString, 'num_results' => 100500];
        $collection = [
            ['query_text' => 'string1', 'num_results' => 1],
            ['query_text' => 'string2', 'num_results' => 2],
            ['query_text' => 'string11', 'num_results' => 11],
            ['query_text' => 'string100', 'num_results' => 100],
            ['query_text' => $queryString, 'num_results' => 100500]
        ];
        $collectionData = [];
        foreach ($collection as $collectionItem) {
            $collectionData[] = new \Magento\Framework\DataObject($collectionItem);
        }

        $this->configHelperMock->expects($this->once())
            ->method('getAutocompleteLimit')
            ->willReturn($limit);

        $this->suggestCollection->expects($this->any())
            ->method('setPageSize')
            ->with($limit)
            ->will($this->returnSelf());

        $this->suggestCollection->expects($this->any())
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
                'string1',
                'string2',
                'string11',
                'string100'
            ));
        $itemMock->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue($expected));

        $this->itemFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($itemMock);

        $this->configHelperMock->expects($this->at(0))
            ->method('isAutocompleteEnabled')
            ->willReturn(true);

        $result = $this->model->getItems();
        $this->assertEquals($expected, $result[0]->toArray());

        $this->configHelperMock->expects($this->once())
            ->method('isAutocompleteEnabled')
            ->willReturn(false);

        $result = $this->model->getItems();

        $this->assertEquals([], $result);
    }
}
