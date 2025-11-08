<?php
namespace Swissup\Askit\Plugin\Model\Page;

use Magento\Ui\Component\Form;
use Magento\Framework\UrlInterface;

use Swissup\Askit\Api\Data\MessageInterface;

class DataProvider
{
    const GROUP_ASKIT   = 'askit';
    const GROUP_CONTENT = 'content';
    const SORT_ORDER    = 120;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @param UrlInterface $urlBuilder
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        UrlInterface $urlBuilder,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
    }

    public function afterGetMeta(
        \Magento\Cms\Model\Page\DataProvider $subject,
        $result
    ) {
        $result[self::GROUP_ASKIT] = [
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
                                'render_url' => $this->urlBuilder->getUrl('mui/index/render', ['current_page_id' => $this->getCurrentPageId()] /* for massaction filter */),
                                'realTimeLink' => false,
                                'behaviourType' => 'simple',
                                'externalFilterMode' => false,
                                'currentPageId' => $this->getCurrentPageId(),
                                'itemTypeId' => MessageInterface::TYPE_CMS_PAGE,
                                'exports' => [
                                    'currentPageId' => '${ $.externalProvider }:params.current_page_id',
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
     * Get current cms page id
     *
     * @return int
     */
    protected function getCurrentPageId()
    {
        $requestId = $this->request->getParam('page_id', false);
        return $requestId;
    }
}
