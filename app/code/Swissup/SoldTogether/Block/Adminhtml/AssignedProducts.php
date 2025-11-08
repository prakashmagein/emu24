<?php

namespace Swissup\SoldTogether\Block\Adminhtml;

use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Swissup\SoldTogether\Model\DataPacker;
use Swissup\SoldTogether\Model\Resolver\ResourceModels as ResourceResolver;

class AssignedProducts extends \Magento\Backend\Block\Template
{
    /**
     * @var array
     */
    private $linkedData;

    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'assign_products.phtml';

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var ResourceResolver
     */
    protected $resourceResolver;

    /**
     * @var DataPacker
     */
    protected $dataPacker;

    /**
     * AssignProducts constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param LocatorInterface                        $locator
     * @param ResourceResolver                        $resourceResolver
     * @param DataPacker                              $dataPacker
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        LocatorInterface $locator,
        ResourceResolver $resourceResolver,
        DataPacker $dataPacker,
        array $data = []
    ) {
        $this->locator = $locator;
        $this->resourceResolver = $resourceResolver;
        $this->dataPacker = $dataPacker;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve instance of grid block
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBlockGrid(string $linkType)
    {
        $name = "soldtogether.{$linkType}.grid";
        $block = $this->getLayout()->getBlock($name);
        if (!$block) {
            $block = $this->getLayout()->createBlock(
                AssignedProducts\Grid::class,
                $name,
                [
                    'data' => [
                        'id' => "soldtogether_{$linkType}_grid",
                        'link_type' => $linkType,
                        'selected_products' => array_keys($this->getLinkedData($linkType)),
                        'current_product_id' => $this->locator->getProduct()->getId(),
                    ]
                ]
            );
        }
        return $block;
    }

    /**
     * Return HTML of grid block
     *
     * @return string
     */
    public function getGridHtml(string $linkType)
    {
        return $this->getBlockGrid($linkType)->toHtml();
    }

    /**
     * @return string
     */
    public function getProductsJson(string $linkType)
    {
        $data = array_map(function ($item) {
            $data = isset($item['data_serialized']) ?
                json_decode($item['data_serialized'], true) :
                [];
            $data['weight'] = $item['weight'] ?? null;

            return array_filter($data);
        }, $this->getLinkedData($linkType));

        return $this->dataPacker->set($data)->packToJson();
    }

    public function getLinkedData(string $linkType): array
    {
        $resource = $this->resourceResolver->get($linkType);
        if (!$resource) {
            return [];
        }

        $product = $this->locator->getProduct();
        $fields = ['related_id', 'weight'];
        if ($linkType == 'order') {
            $fields[] = 'data_serialized';
        }

        $linkedData = $resource->readLinkedData(
            $product->getId(),
            $fields
        );

        return $linkedData ?: [];
    }

    public function fallbackSecureRenderTag(
        string $tagName,
        string $content
    ): string {
        switch ($tagName) {
            case 'script':
                return "<script type=\"text/javascript\">{$content}</script>";

                break;
        }

        return '';
    }
}
