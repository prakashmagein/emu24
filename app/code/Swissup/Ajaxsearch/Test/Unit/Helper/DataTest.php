<?php

namespace Swissup\Ajaxsearch\Test\Unit\Helper;

use Swissup\Ajaxsearch\Helper\Data;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Swissup\Ajaxsearch\Model\Config\Source\Design\FormLayout;

/**
 * Unit test for \Swissup\Swissup\Block\Init
 */
class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Swissup\Ajaxsearch\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManagerMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfigMock;

    public function setUp(): void
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMockForAbstractClass();

        $this->dataHelper = $helper->getObject(
            Data::class,
            [
                'storeManager' => $this->storeManagerMock,
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
    }

    public function testGetLimit()
    {
        $value = '5';
        $expected = 5;
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Data::CONFIG_PATH_LIMIT, ScopeInterface::SCOPE_STORE, null)
            ->willReturn($value);

        $this->assertEquals($expected, $this->dataHelper->getLimit());
    }

    /**
     * @param string $value
     * @param array $expected
     * @dataProvider isHintDataProvider
     */
    public function testIsHint($value, $expected)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Data::CONFIG_PATH_HINT, ScopeInterface::SCOPE_STORE, null)
            ->willReturn($value);

        $this->assertEquals(
            $expected,
            $this->dataHelper->isHint()
        );
    }

    /**
     * @return array
     */
    public function isHintDataProvider()
    {
        return [[0, false], [1, true]];
    }

    /**
     * @param string $value
     * @param array $expected
     * @dataProvider isHighligthDataProvider
     */
    public function testIsHighligth($value, $expected)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Data::CONFIG_PATH_HIGHLIGHT, ScopeInterface::SCOPE_STORE, null)
            ->willReturn($value);

        $this->assertEquals($expected, $this->dataHelper->isHighligth());
    }

    /**
     * @return array
     */
    public function isHighligthDataProvider()
    {
        return [[0, false], [1, true]];
    }

    /**
     * @param string $value
     * @param array $expected
     * @dataProvider getClassNamesDataProvider
     */
    public function testGetClassNames($value, $expected)
    {
        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with(Data::CONFIG_PATH_CLASSNAMES, ScopeInterface::SCOPE_STORE, null)
            ->willReturn($value);

        if (isset($value['dataset'])) {
            $this->scopeConfigMock->expects($this->at(1))
                ->method('getValue')
                ->with(Data::CONFIG_PATH_RESULTS_LAYOUT, ScopeInterface::SCOPE_STORE, null)
                ->willReturn('grid');
        }

        $this->assertEquals($expected, $this->dataHelper->getClassNames());
    }

    /**
     * @return array
     */
    public function getClassNamesDataProvider()
    {
        return [
            [
                [
                'input'=> 'tt-input',
                // 'hint'=> 'tt-hint',
                // 'menu'=> 'tt-menu block-swissup-ajaxsearch-results',
                // 'dataset'=> 'tt-dataset products wrapper list products-list',
                // 'suggestion'=> 'tt-suggestion',
                // 'empty'=> 'tt-empty',
                // 'open'=> 'tt-open',
                // 'cursor'=> 'tt-cursor',
                // 'highlight'=> 'tt-highlight'
                ],
                ['input'=> 'tt-input']
                // '{"input":"tt-input"}'
            ],
            [
                [
                'input'=> 'tt-input',
                'dataset'=> 'tt-dataset wrapper',
                ],
                ['input'=> 'tt-input', 'dataset'=> 'tt-dataset wrapper grid']
                // '{"input":"tt-input"}'
            ],
            [[], []]
        ];
    }

    /**
     * @param string $value
     * @param boolean $expected
     * @dataProvider isFoldedDesignEnabledProvider
     */
    public function testIsFoldedDesignEnabled($value, $expected)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Data::CONFIG_PATH_FORM_LAYOUT, ScopeInterface::SCOPE_STORE, null)
            ->willReturn($value);

        $this->assertEquals(
            $expected,
            $this->dataHelper->isFoldedDesignEnabled()
        );
    }

    /**
     * @return array
     */
    public function isFoldedDesignEnabledProvider()
    {
        return [
            [FormLayout::DEFAULT, false],
            [FormLayout::FOLDED_INLINE, true],
            [FormLayout::FOLDED_FULLSCREEN, true],
        ];
    }

    /**
     * @param string $value
     * @param boolean $expected
     * @dataProvider isFullscreenLayoutEnabledProvider
     */
    public function testIsFullscreenLayoutEnabled($value, $expected)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Data::CONFIG_PATH_FORM_LAYOUT, ScopeInterface::SCOPE_STORE, null)
            ->willReturn($value);

        $this->assertEquals(
            $expected,
            $this->dataHelper->isFullscreenLayoutEnabled()
        );
    }

    /**
     * @return array
     */
    public function isFullscreenLayoutEnabledProvider()
    {
        return [
            [FormLayout::DEFAULT, false],
            [FormLayout::FOLDED_INLINE, false],
            [FormLayout::FOLDED_FULLSCREEN, true],
        ];
    }

    /**
     * @return array
     */
    public function isEnabledProvider()
    {
        return [[false, false], [true, true]];
    }

    /**
     * @param string $value
     * @param string $expected
     * @dataProvider getAdditionalCssClassProvider
     */
    public function testGetAdditionalCssClass($value, $expected)
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(Data::CONFIG_PATH_FORM_LAYOUT, ScopeInterface::SCOPE_STORE, null)
            ->willReturn($value);

        $this->assertEquals(
            $expected,
            $this->dataHelper->getAdditionalCssClass()
        );
    }

    /**
     * @return array
     */
    public function getAdditionalCssClassProvider()
    {
        return [
            [FormLayout::DEFAULT, ''],
            [FormLayout::FOLDED_INLINE, 'folded inline'],
            [FormLayout::FOLDED_FULLSCREEN, 'folded fullscreen'],
        ];
    }

    /**
     * @param string $value
     * @param boolean $expected
     * @dataProvider isEnabledProvider
     */
    public function testIsAutocompleteEnabled($value, $expected)
    {
        $this->scopeConfigMock->expects($this->at(0))
            ->method('isSetFlag')
            ->with(Data::CONFIG_PATH_AUTOCOMPLETE_ENABLE, ScopeInterface::SCOPE_STORE, null)
            ->willReturn($value);

        if ($value === false) {
            $this->scopeConfigMock->expects($this->at(1))
                ->method('isSetFlag')
                ->with(Data::CONFIG_XML_PATH_ENABLE, ScopeInterface::SCOPE_STORE, null)
                ->willReturn(true);
        }

        $this->assertEquals(
            $expected,
            $this->dataHelper->isAutocompleteEnabled()
        );
    }

    public function testGetAutocompleteLimit()
    {
        $value = '5';
        $expected = 5;
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Data::CONFIG_PATH_AUTOCOMPLETE_LIMIT, ScopeInterface::SCOPE_STORE, null)
            ->willReturn($value);

        $this->assertEquals($expected, $this->dataHelper->getAutocompleteLimit());
    }

    /**
     * @param string $value
     * @param boolean $expected
     * @dataProvider isEnabledProvider
     */
    public function testIsProductEnabled($value, $expected)
    {
        $this->scopeConfigMock->expects($this->at(0))
            ->method('isSetFlag')
            ->with(Data::CONFIG_PATH_PRODUCT_ENABLE, ScopeInterface::SCOPE_STORE, null)
            ->willReturn($value);

        if ($value) {
            $this->scopeConfigMock->expects($this->at(1))
                ->method('isSetFlag')
                ->with(Data::CONFIG_XML_PATH_ENABLE, ScopeInterface::SCOPE_STORE, null)
                ->willReturn(true);
        }

        $this->assertEquals(
            $expected,
            $this->dataHelper->isProductEnabled()
        );
    }

    public function testGetProductLimit()
    {
        $value = '5';
        $expected = 5;
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Data::CONFIG_PATH_PRODUCT_LIMIT, ScopeInterface::SCOPE_STORE, null)
            ->willReturn($value);

        $this->assertEquals($expected, $this->dataHelper->getProductLimit());
    }

    /**
     * @param string $value
     * @param boolean $expected
     * @dataProvider isEnabledProvider
     */
    public function testIsCategoryEnabled($value, $expected)
    {
        $this->scopeConfigMock->expects($this->at(0))
            ->method('isSetFlag')
            ->with(Data::CONFIG_PATH_CATEGORY_ENABLE, ScopeInterface::SCOPE_STORE, null)
            ->willReturn($value);

        if ($value) {
            $this->scopeConfigMock->expects($this->at(1))
                ->method('isSetFlag')
                ->with(Data::CONFIG_XML_PATH_ENABLE, ScopeInterface::SCOPE_STORE, null)
                ->willReturn(true);
        }

        $this->assertEquals(
            $expected,
            $this->dataHelper->isCategoryEnabled()
        );
    }

    public function testGetCategoryLimit()
    {
        $value = '5';
        $expected = 5;
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Data::CONFIG_PATH_CATEGORY_LIMIT, ScopeInterface::SCOPE_STORE, null)
            ->willReturn($value);

        $this->assertEquals($expected, $this->dataHelper->getCategoryLimit());
    }

    /**
     * @param string $value
     * @param boolean $expected
     * @dataProvider isEnabledProvider
     */
    public function testIsPageEnabled($value, $expected)
    {
        $this->scopeConfigMock->expects($this->at(0))
            ->method('isSetFlag')
            ->with(Data::CONFIG_PATH_PAGE_ENABLE, ScopeInterface::SCOPE_STORE, null)
            ->willReturn($value);

        if ($value) {
            $this->scopeConfigMock->expects($this->at(1))
                ->method('isSetFlag')
                ->with(Data::CONFIG_XML_PATH_ENABLE, ScopeInterface::SCOPE_STORE, null)
                ->willReturn(true);
        }

        $this->assertEquals(
            $expected,
            $this->dataHelper->isPageEnabled()
        );
    }

    public function testGetPageLimit()
    {
        $value = '5';
        $expected = 5;
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Data::CONFIG_PATH_PAGE_LIMIT, ScopeInterface::SCOPE_STORE, null)
            ->willReturn($value);

        $this->assertEquals($expected, $this->dataHelper->getPageLimit());
    }
}
