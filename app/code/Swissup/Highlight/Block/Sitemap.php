<?php

namespace Swissup\Highlight\Block;

use Magento\Framework\View\Element\Template;
use Swissup\Highlight\Model\Page\Collection;

class Sitemap extends Template
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @param Collection       $collection
     * @param Template\Context $context
     * @param array            $data
     */
    public function __construct(
        Collection $collection,
        Template\Context $context,
        array $data = []
    ) {
        $this->collection = $collection;
        parent::__construct($context, $data);
    }

    /**
     * @return Collection
     */
    public function getCollection()
    {
        $collection = clone $this->collection;
        $collection->clear()->setOrder('title', 'ASC');

        return $collection;
    }

    /**
     * @param  \Magento\Framework\DataObject $page
     * @return string
     */
    public function getItemUrl($page)
    {
        return $this->_urlBuilder->getUrl(null, ['_direct' => $page->getUrl()]);
    }

    /**
     * @param  \Magento\Framework\DataObject $page
     * @return string
     */
    public function getItemName($page)
    {
        return $page->getTitle();
    }
}
