<?php
namespace Swissup\Askit\Plugin\Model\Category;

use Magento\Ui\Component\Form;
use Magento\Store\Model\Store;

use Swissup\Askit\Api\Data\MessageInterface;

class DataProvider
{
    const GROUP_ASKIT   = 'askit';
    const GROUP_CONTENT = 'content';
    const SORT_ORDER    = 120;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    private $categoryRepository;

    /**
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Catalog\Model\CategoryRepository $categoryRepository
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->coreRegistry = $registry;
        $this->request = $request;
        $this->categoryRepository = $categoryRepository;
    }

    public function afterGetMeta(
        \Magento\Catalog\Model\Category\DataProvider $subject,
        $result
    ) {
        $result['askit'] = [
            'children' => [
                'askit_question_listing' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'autoRender' => true,
                                'componentType' => 'insertListing',
                                'dataScope' => 'askit_question_listing',
                                'externalProvider' => 'askit_question_listing.askit_question_listing_data_source',
                                'selectionsProvider' => 'askit_question_listing.askit_question_listing.askit_message_columns.ids',
                                'ns' => 'askit_question_listing',
                                'render_url' => $this->urlBuilder->getUrl('mui/index/render', ['current_category_id' => $this->getCurrentCategoryId()] /* for massaction filter */),
                                'realTimeLink' => false,
                                'behaviourType' => 'simple',
                                'externalFilterMode' => false,
                                'currentCategoryId' => $this->getCurrentCategoryId(),
                                'itemTypeId' => MessageInterface::TYPE_CATALOG_CATEGORY,
                                'exports' => [
                                    'currentCategoryId' => '${ $.externalProvider }:params.current_category_id',
                                    'itemTypeId' => '${ $.externalProvider }:params.item_type_id'
                                ]
                            ],
                        ],
                    ],
                ],
            ],
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Questions'),
                        'collapsible' => true,
                        'opened' => false,
                        'componentType' => Form\Fieldset::NAME,
                        'sortOrder' => static::SORT_ORDER
                    ],
                ],
            ],
        ];
        return $result;
    }

    /**
     * Get current category id
     *
     * @return int
     */
    protected function getCurrentCategoryId()
    {
        $category = $this->coreRegistry->registry('category');
        if ($category) {
            return $category->getId();
        }
        $categoryId = $this->request->getParam('id', false);
        $storeId = $this->request->getParam(
            'store',
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        );
        if ($categoryId) {
            $category = $this->categoryRepository->get($categoryId, $storeId);
            // if (!$category->getId()) {
            //     return;
            //     // throw NoSuchEntityException::singleField('id', $categoryId);
            // }
        }
        return $category->getId();
    }
}
