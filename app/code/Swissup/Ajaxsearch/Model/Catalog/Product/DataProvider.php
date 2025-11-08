<?php
namespace Swissup\Ajaxsearch\Model\Catalog\Product;

use Swissup\Ajaxsearch\Model\DataProvider\AbstractDataProvider;
use Magento\Search\Model\Autocomplete\DataProviderInterface;
use Magento\Search\Model\ResourceModel\Query\Collection;
use Swissup\Ajaxsearch\Model\Query\Catalog\Product as Query;
use Swissup\Ajaxsearch\Model\QueryFactory;
use Magento\Search\Model\Autocomplete\ItemFactory;
use Swissup\Ajaxsearch\Helper\Data as ConfigHelper;
use Magento\Catalog\Block\Product\ImageFactory;
use Magento\Store\Model\StoreManagerInterface;

class DataProvider extends AbstractDataProvider implements DataProviderInterface
{
    /**
     * @var ImageFactory
     */
    protected $imageFactory;

    /**
     *
     * @var \Magento\Catalog\Helper\Data
     */
    private $taxHelper;

    /**
     * @param QueryFactory $queryFactory
     * @param ItemFactory $itemFactory
     * @param ConfigHelper $configHelper
     * @param ImageFactory $imageBuilder
     * @param \Magento\Catalog\Helper\Data $taxHelper
     */
    public function __construct(
        QueryFactory $queryFactory,
        ItemFactory $itemFactory,
        ConfigHelper $configHelper,
        ImageFactory $imageFactory,
        \Magento\Catalog\Helper\Data $taxHelper
    ) {
        parent::__construct($queryFactory, $itemFactory, $configHelper);

        $this->imageFactory = $imageFactory;
        $this->taxHelper = $taxHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $enable = $this->configHelper->isProductEnabled();
        if (!$enable) {
            return [];
        }
        $this->queryFactory->setInstanceName(Query::class);
        $collection = $this->getSuggestCollection();
        $query = $this->getQuery();

        $result = [];
        $isDebug = false;
        if ($isDebug) {
            // @see Magento\Framework\Search\Adapter\Mysql\Adapter::query for see temp table query
            $_select = (string) $collection->getSelect();
            $result[] = $this->itemFactory->create([
                '_type' => 'debug',
                'title' => '',
                'num_results' => '',
                '_query' => $query->getQueryText(),
                '_class' => get_class($collection),
                '_num_results' => $collection->getSize(),
                '_select' => hash('sha1', $_select) . '  ' . $_select,
            ]);
        }

        foreach ($collection as $item) {
            // $item = $item->load($item->getId());
            $imageHtml = $this->getImageBlockHtml($item);

            $resultItem = $this->itemFactory->create(
                array_merge($item->getData(), [
                    '_type' => 'product',
                    'title' => $item->getName(),
                    'num_results' => '',
                    'image_html' => $imageHtml,
                    'url' => $item->getProductUrl(),
                    'final_price' => $this->getFinalPrice($item),
                ])
            );
            if ($resultItem->getTitle() == $query->getQueryText()) {
                array_unshift($result, $resultItem);
            } else {
                $result[] = $resultItem;
            }
        }
        return $result;
    }

    /**
     * @param $product
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getImageBlockHtml($product)
    {
        $imageBlock = $this->getImageBlock($product);
        $imageHtml = $imageBlock ? $imageBlock->toHtml() : '';

        return $imageHtml;
    }

    /**
     * Retrieve product image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    private function getImageBlock($product, $imageId = null, $attributes = [])
    {
        if (!$imageId) {
            $imageId = $this->getDefaultImageId();
        }
        /** @var \Magento\Catalog\Block\Product\Image $imageBlock */
        $imageBlock = $this->imageFactory->create($product, $imageId, $attributes);

        $imageBlock->setTemplate('Swissup_Ajaxsearch::product/image.phtml');

        return $imageBlock;
    }

    /**
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return float
     */
    private function getFinalPrice($product)
    {
//        $finalPrice  = $product->getFinalPrice() > 0 ?
//            $product->getFinalPrice() : $product->getPriceInfo()->getPrice('final_price')->getValue();
        $finalPrice  = $product->getPriceInfo()->getPrice('final_price')->getValue();

        $incTax = $this->configHelper->isTaxIncludingToPrice();
        if ($incTax) {
            $finalPrice = $this->taxHelper->getTaxPrice($product, $finalPrice, true);
        }

        return (float) $finalPrice;
    }

    /**
     * @return string
     */
    private function getDefaultImageId()
    {
        $layout = $this->configHelper->getResultsLayout();

        if ($layout === 'grid') {
            return 'category_page_grid';
        }

        return 'product_page_image_small';
    }
}
