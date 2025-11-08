<?php
namespace Swissup\Amp\Plugin\Block;

class Topmenu
{
    /**
     * @var \Swissup\Amp\Helper\Data
     */
    protected $helper;

    /**
     * @param \Swissup\Amp\Helper\Data $helper
     */
    public function __construct(
        \Swissup\Amp\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Modify top menu html for amp
     *
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @param string $result
     * @return string
     */
    public function afterGetHtml(
        \Magento\Theme\Block\Html\Topmenu $subject,
        string $result
    ) {
        if (!$this->helper->isVarnishRequest()) {
            if (!$this->helper->canUseAmp() || empty($result)) {
                return $result;
            }
        }

        $result = $this->helper->prepareDOMDocumentHtml($result);

        libxml_use_internal_errors(true);
        $document = new \DOMDocument();
        $document->loadHTML($result);

        // find all li items with class="parent"
        $xpath = new \DOMXPath($document);
        $nodes = $xpath->query(
            "//li[contains(concat(' ', normalize-space(@class), ' '), ' parent ')]",
            $document
        );

        $counter = 1;
        foreach ($nodes as $node) {
            $classes = explode(' ', $node->getAttribute('class'));
            $link = $node->childNodes->item(0);
            $ul = $node->childNodes->item(1);

            // create checkbox
            $check = $document->createElement('input');
            $check->setAttribute('id', 'category-node-' . $counter);
            $check->setAttribute('type', 'checkbox');
            if (array_intersect(['has-active', 'active'], $classes)) {
                $check->setAttribute('checked', '');
            }

            // create checkbox label
            $label = $document->createElement('label');
            $label->setAttribute('for', 'category-node-' . $counter);
            $label->setAttribute('class', 'has-children');
            $label->textContent = $link->textContent;

            // replace link with checkbox and label
            $node->replaceChild($label, $link);
            $node->insertBefore($check, $label);

            // create view all link
            $viewAll = $document->createElement('li');
            if (in_array('active', $classes)) {
                $viewAll->setAttribute('class', 'view-all active');
            } else {
                $viewAll->setAttribute('class', 'view-all');
            }

            $viewAllLink = $document->createElement('a');
            $viewAllLink->setAttribute('href', $link->getAttribute('href'));
            $viewAllLink->textContent = __('View All') . ' ' . $link->textContent;

            $viewAll->appendChild($viewAllLink);
            $ul->insertBefore($viewAll, $ul->childNodes->item(0));

            $counter++;
        }

        return $document->saveHTML();
    }

    /**
     * Add amp cache key
     *
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @param array $result
     * @return array
     */
    public function afterGetCacheKeyInfo(
        \Magento\Theme\Block\Html\Topmenu $subject,
        array $result
    ) {
        if ($this->helper->canUseAmp()) {
            $result[] = 'swissupamp';
        }

        return $result;
    }
}
