<?php
namespace Swissup\Askit\Block\Adminhtml\Customer\Edit\Tab;

// namespace Magento\Wishlist\Block\Adminhtml;

use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;
use Magento\Ui\Component\Layout\Tabs\TabWrapper;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Magento\Customer\Controller\RegistryConstants;

class Questions extends TabWrapper implements TabInterface
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * @var bool
     */
    protected $isAjaxLoaded = true;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(Context $context, Registry $registry, array $data = [])
    {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    public function getCustomerId()
    {
        return $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return $this->getCustomerId();
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return (string) __('Questions');
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('askit/question/grid', [
            '_current' => true,
            'item_type_id' => 'customer',
            'customer_id' => $this->getCustomerId()
        ]);
    }
}
