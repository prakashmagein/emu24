<?php
namespace Swissup\Reviewreminder\Block\Adminhtml\System\Config\Fieldset;

use Magento\Framework\Data\Form\Element\AbstractElement;

class RunIndexing extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function render(AbstractElement $element)
    {
        $url  = $this->getUrl('reviewreminder/index/indexOrders');
        $runText = __("Run");
        return <<<HTML
<tr>
    <td colspan="100">
        <div class="button-container">
            <button id="run-orders-indexing" class="button action-configure" type="button"><span>$runText</span></button>
        </div>
        <script type="text/javascript">
            require([
                'jquery',
                'reminderIndexing'
            ], function ($, reminderIndexing) {
                reminderIndexing.init("$url", '#run-orders-indexing');
            });
        </script>
    </td>
</tr>
HTML;
    }
}
