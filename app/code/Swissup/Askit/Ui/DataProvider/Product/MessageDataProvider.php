<?php
namespace Swissup\Askit\Ui\DataProvider\Product;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\App\RequestInterface;
use Swissup\Askit\Model\ResourceModel\Message\Collection;

/**
 * Class MessageDataProvider
 *
 */
class MessageDataProvider extends AbstractDataProvider
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $collection = $this->getCollectionModel();
        // $customerId = $this->request->getParam('current_customer_id', false);
        // if ($customerId) {
        //     $collection->addCustomerFilter($customerId);
        // }
        $productId = $this->request->getParam('current_product_id', false);
        if ($productId) {
            $collection->addProductFilter($productId);
        }

        $categoryId = $this->request->getParam('current_category_id', false);

        if ($categoryId) {
            $collection->addCategoryFilter($categoryId);
        }

        $pageId = $this->request->getParam('current_page_id', false);
        if ($pageId) {
            $collection->addPageFilter($pageId);
        }

        $arrItems = [
            'totalRecords' => $collection->getSize(),
            'items' => [],
        ];

        foreach ($collection as $item) {
            $data = $item->toArray([]);
            if (isset($data['store_id']) && !is_array($data['store_id'])) {
                $data['store_id'] = [$data['store_id']];
            }

            $arrItems['items'][] = $data;
        }

        return $arrItems;
    }

    /**
     * Return collection
     *
     * @return \Swissup\Askit\Model\ResourceModel\Message\Collection
     */
    public function getCollectionModel()
    {
        return $this->getCollection();
    }
}
