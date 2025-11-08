<?php

namespace Swissup\Askit\Block\Question;

use Swissup\Askit\Api\Data\MessageInterface;
use Swissup\Askit\Block\Question\AbstractBlock;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Url;
use Magento\Store\Model\Store;

// use Magento\Review\Model\ResourceModel\Rating\Collection as RatingCollection;

class Listing extends AbstractBlock
{
    const DEFAULT_QUESTION_VIEW_TEMPLATE = 'question/view.phtml';
    const DEFAULT_ANSWER_FORM_TEMPLATE = 'question/answer/form.phtml';
    const DEFAULT_ANSWER_VIEW_TEMPLATE = 'question/answer/view.phtml';

   /**
    *
    * @var \Swissup\Askit\Model\ResourceModel\Question\CollectionFactory
    */
    protected $collectionFactory;

    /**
     *
     * @var \Swissup\Askit\Model\ResourceModel\Question\Collection
     */
    protected $collection;

    /**
     * @var \Swissup\Askit\Model\Message\Source\PublicStatuses
     */
    private $publicStatuses;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    private $urlEncoder;

    /**
     * @param Context                                                       $context
     * @param \Swissup\Askit\Model\ResourceModel\Question\CollectionFactory $collectionFactory
     * @param \Swissup\Askit\Model\Message\Source\PublicStatuses            $publicStatuses
     * @param \Magento\Framework\Serialize\SerializerInterface              $serializer
     * @param \Magento\Framework\Url\EncoderInterface                       $urlEncoder
     * @param array                                                         $data
     */
    public function __construct(
        Context $context,
        \Swissup\Askit\Model\ResourceModel\Question\CollectionFactory $collectionFactory,
        \Swissup\Askit\Model\Message\Source\PublicStatuses $publicStatuses,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collectionFactory = $collectionFactory;
        $this->publicStatuses = $publicStatuses;
        $this->serializer = $serializer;
        $this->urlEncoder = $urlEncoder;

        $this->setTabTitle();
    }

    /**
     * @return Listing
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        $layout = $this->getLayout();

        /** @var \Magento\Framework\View\Element\AbstractBlock $blockQuestionView */
        $blockQuestionView = $layout->createBlock(\Swissup\Askit\Block\Question\View::class);
        $blockQuestionView->setTemplate(self::DEFAULT_QUESTION_VIEW_TEMPLATE)
            ->setPublicStatuses($this->getPublicStatuses());
        if ($blockQuestionView) {
            $this->setChild('askit_question_view', $blockQuestionView);
        }

        /** @var \Magento\Framework\View\Element\AbstractBlock $blockAnswerForm */
        $blockAnswerForm = $layout->createBlock(\Swissup\Askit\Block\Question\Answer\Form::class);
        $blockAnswerForm->setTemplate(self::DEFAULT_ANSWER_FORM_TEMPLATE);
        if ($blockAnswerForm) {
            $this->setChild('askit_answer_form', $blockAnswerForm);
        }

        /** @var \Magento\Framework\View\Element\AbstractBlock $blockAnswerView */
        $blockAnswerView = $layout->createBlock(\Swissup\Askit\Block\Question\Answer\View::class);
        $blockAnswerView->setTemplate(self::DEFAULT_ANSWER_VIEW_TEMPLATE);
        if ($blockAnswerView) {
            $this->setChild('askit_answer_view', $blockAnswerView);
        }

        /** @var \Magento\Framework\View\Element\AbstractBlock $pager */
        $pager = $layout->createBlock(\Magento\Theme\Block\Html\Pager::class);
        if ($pager) {
            $pager
                // ->setAvailableLimit([1 => 1, 10 => 10, 20 => 20, 50 => 50])
                // ->setLimit(10)
                ->setCollection($this->getCollection());
            $this->setChild('pager', $pager);
        }

        // Add canonical URL fro question pages
        if ($this->getRequest()->getFullActionName() === 'askit_index_index') {
            $this->pageConfig->addRemotePageAsset(
                $this->getUrl('', ['_use_rewrite' => true]),
                'canonical',
                [
                    'attributes' => [
                        'rel' => 'canonical'
                    ]
                ]
            );
        }

        return parent::_prepareLayout();
    }

    /**
     *
     * @return \Swissup\Askit\Model\ResourceModel\Question\Collection
     */
    public function getCollection()
    {
        if (empty($this->collection)) {
            $storeId = $this->_storeManager->getStore()->getId();

            $collection = $this->collectionFactory->create()
                ->addStoreFilter([$storeId, Store::DEFAULT_STORE_ID])
                ->addQuestionFilter(0)
                ->addHintOrder()
                ->addCreatedTimeOrder()
                ;
            $questionId = $this->getRequest()->getParam('questionId', false);
            if ($questionId !== false) {
                $collection->addIdFilter((int)$questionId);
            }

            $type = $this->getItemTypeId();
            switch ($type) {
                case MessageInterface::TYPE_CATALOG_CATEGORY:
                    $itemId = $this->getCategoryId();
                    $collection->addCategoryFilter($itemId);
                    break;
                case MessageInterface::TYPE_CMS_PAGE:
                    $itemId = $this->getPageId();
                    $collection->addPageFilter($itemId);
                    break;
                case MessageInterface::TYPE_CATALOG_PRODUCT:
                    $itemId = $this->getProductId();
                    $collection->addProductFilter($itemId);
                    break;
                case MessageInterface::TYPE_UNKNOWN:
                default:
                    break;
            }

            if ($this->isCustomerLoggedIn()) {
                $collection->addPrivateFilter($this->getCustomerSession()->getId());
                $collection->addFieldToFilter(
                    ['customer_id', 'status'],
                    [$this->getCustomerSession()->getId(), ['in' => $this->getPublicStatuses()]]
                );
            } else {
                $collection->addPrivateFilter();
                $collection->addStatusFilter($this->getPublicStatuses());
            }

            $this->collection = $collection;
        }
        return $this->collection;
    }

    /**
     * Set tab title
     *
     * @return void
     */
    public function setTabTitle()
    {
        $title = $this->getCollectionSize()
            ? __('Questions %1', '<span class="counter">' . $this->getCollectionSize() . '</span>')
            : __('Questions');
        $this->setTitle($title);
    }

    /**
     * Get size of questions collection
     *
     * @return int
     */
    public function getCollectionSize()
    {
        return $this->getCollection()->getSize();
    }

    /**
     * @return array
     */
    public function getPublicStatuses()
    {
        $openStatuses = $this->publicStatuses->getOptionArray();
        return array_keys($openStatuses);
    }

    /**
     * Fix to integrate AskIt Listing with SEO Pager module
     *
     * @return \Magento\Theme\Block\Html\Pager
     */
    public function getToolbarBlock()
    {
        /** @var \Magento\Theme\Block\Html\Pager $pager */
        $pager = $this->getChildBlock('pager');
        return $pager;
    }

    /**
     * @return int|null
     */
    private function getCurrentPage()
    {
        $pager = $this->getToolbarBlock();
        $currentPage = $pager && $pager->getCurrentPage() > 1 ? $pager->getCurrentPage() : null;

        return $currentPage;
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        $currentPage = $this->getCurrentPage();
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();
        $alias = $request->getAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS);

        $currentUrl = $this->_urlBuilder->getCurrentUrl();
        $referUrlParam = $this->urlEncoder->encode($currentUrl);

        $queryParams = [
            'p' => $currentPage,
            \Magento\Customer\Model\Url::REFERER_QUERY_PARAM_NAME => $referUrlParam
        ];

        if ($request->getRouteName() === 'askit') {
            $ajaxUrl = $this->getUrl('', [
                '_current' => true,
                '_use_rewrite' => true,
                '_query' => $queryParams
            ]);
        } elseif ($alias) {
            $ajaxUrl = $this->getUrl('', [
                '_direct' => "questions/{$alias}",
                '_query' => $queryParams
            ]);
        } else {
            $ajaxUrl = $this->getUrl('askit/index/index', [
                'item_type_id' => $this->getItemTypeId(),
                'id' => $this->getItemId(),
                '_query' => $queryParams
            ]);
        }

        return $ajaxUrl;
    }

    /**
     * Retrieve serialized JS layout configuration ready to use in template
     *
     * @return string
     */
    public function getAjaxJsLayout()
    {
        return $this->serializer->serialize([
            'questionsUrl' => $this->getAjaxUrl()
        ]);
    }
}
