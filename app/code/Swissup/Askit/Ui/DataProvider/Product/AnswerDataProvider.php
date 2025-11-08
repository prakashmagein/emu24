<?php
namespace Swissup\Askit\Ui\DataProvider\Product;

use Magento\Framework\App\RequestInterface;
use Swissup\Askit\Ui\DataProvider\Product\MessageDataProvider;
use Swissup\Askit\Model\ResourceModel\Answer\Grid\CollectionFactory;
use Swissup\Askit\Model\ResourceModel\Answer\Grid\Collection;

/**
 * Class AnswerDataProvider
 *
 */
class AnswerDataProvider extends MessageDataProvider
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collectionFactory = $collectionFactory;
        $this->collection = $this->collectionFactory->create();
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $this->getCollectionModel()->addAnswerFilter();

        return parent::getData();
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ($filter->getField() === 'question_text') {
            $this->getCollectionModel()->joinQuestionData();
        }

        return parent::addFilter($filter);
    }

    /**
     * Return collection
     *
     * @return \Swissup\Askit\Model\ResourceModel\Answer\Grid\Collection
     */
    public function getCollectionModel()
    {
        return $this->getCollection();
    }
}
