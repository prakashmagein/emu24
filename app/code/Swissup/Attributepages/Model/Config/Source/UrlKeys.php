<?php
namespace Swissup\Attributepages\Model\Config\Source;

class UrlKeys implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory
     */
    protected $attrPageCollectionFactory;
    /**
     * @var \Swissup\Attributepages\Model\ResourceModel\Entity\Collection
     */
    protected $attrPageCollection;
    /**
     * Constructor
     *
     * @param \Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory $attrPageCollectionFactory
     */
    public function __construct(
        \Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory $attrPageCollectionFactory
    )
    {
        $this->attrPageCollectionFactory = $attrPageCollectionFactory;
    }
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $pages = $this->getPagesCollection();
        $urlKeys = [];
        $added = [];
        foreach ($pages as $page) {
            if (in_array($page->getIdentifier(), $added)) {
                continue;
            }

            $added[] = $page->getIdentifier();
            $urlKeys[] = ['value' => $page->getIdentifier(), 'label' => __($page->getName())];
        }
        return $urlKeys;
    }
    /**
     * Get attribute pages collection
     * @return \Swissup\Attributepages\Model\ResourceModel\Entity\Collection
     */
    private function getPagesCollection()
    {
        if ($this->attrPageCollection == null) {
            $this->attrPageCollection = $this->attrPageCollectionFactory
                ->create()
                ->addAttributeOnlyFilter();
        }

        return $this->attrPageCollection;
    }
}
