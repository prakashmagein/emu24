<?php
namespace Swissup\Amp\Model\Html;

class Filter
{
    /**
     * @var \Swissup\Amp\Helper\Data
     */
    protected $helper;

    /**
     * @var array
     */
    protected $domFilters;

    /**
     * @var array
     */
    protected $stringFilters;

    /**
     * @param \Swissup\Amp\Helper\Data $helper
     * @param array $domFilters
     * @param array $stringFilters
     */
    public function __construct(
        \Swissup\Amp\Helper\Data $helper,
        array $domFilters = [],
        array $stringFilters = []
    ) {
        $this->helper = $helper;
        $this->domFilters = $domFilters;
        $this->stringFilters = $stringFilters;
    }

    /**
     * Prepare AMP-compatible html markup and add AMP components
     *
     * @param  string $html Rendered html
     * @return string       Processed html
     */
    public function process($html)
    {
        $html = $this->helper->prepareDOMDocumentHtml($html);

        libxml_use_internal_errors(true);
        $document = new \DOMDocument();
        $document->loadHTML($html);

        foreach ($this->domFilters as $filterFactory) {
            $filterFactory->create()->process($document);
        }

        $output = $document->saveHTML();

        foreach ($this->stringFilters as $filterFactory) {
            $output = $filterFactory->create()->process($output);
        }

        return $output;
    }
}
