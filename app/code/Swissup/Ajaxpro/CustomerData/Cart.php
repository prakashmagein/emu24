<?php

namespace Swissup\Ajaxpro\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Swissup\Ajaxpro\CustomerData\AbstractSectionData;

class Cart extends AbstractSectionData implements SectionSourceInterface
{
    /**
     * @var \Magento\Quote\Model\Quote|null
     */
    protected $quote = null;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\View\Layout\BuilderFactory $layoutBuilderFactory
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\View\Page\Layout\Reader $pageLayoutReader
     * @param \Swissup\Ajaxpro\Helper\Config $configHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        \Swissup\Ajaxpro\Model\View\LayoutFactory $layoutFactory,
        \Magento\Framework\View\Layout\BuilderFactory $layoutBuilderFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\View\Page\Layout\Reader $pageLayoutReader,
        \Swissup\Ajaxpro\Helper\Config $configHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        parent::__construct(
            $layoutFactory,
            $layoutBuilderFactory,
            $context,
            $pageLayoutReader,
            $configHelper,
            $data
        );
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        if (!$this->configHelper->isCartViewEnabled() ||
            !in_array('ajaxpro-cart', $this->getSectionNames())
        ) {
            return [];
        }

        $checkoutCartHandle = $this->configHelper->getCartHandle();
        if (!$this->getQuote() || !$this->getQuote()->getId()) {
            return [
                'checkout.cart' => $this->setHandles([$checkoutCartHandle])->getBlockHtml('checkout.cart')
            ];
        }
        $isSimpleCart = $checkoutCartHandle !== 'ajaxpro_popup_checkout_cart_index_fixes';
        $return  = [
            // 'params' => $ajaxpro,
            // 'test' => md5(time()),
            'checkout.cart' => $this->setHandles([$checkoutCartHandle])
                ->getBlockHtml('checkout.cart'),
            'checkout.cart.fixes' => $isSimpleCart ? '' :
                $this->setHandles(['ajaxpro_popup_checkout_cart_index_fixes'])
                    ->getBlockHtml('ajaxpro.checkout.cart.fixes'),
            'reinit' => $this->setHandles(['default'])
                ->getBlockHtml('ajaxpro.init')
        ];

        // foreach ($return as $key => &$block) {
        //     $block .= '<script type="text/javascript">console.log("'
        //         . $key . ' ' . md5($block)
        //         . '");</script>';
        // }
        $this->flushLayouts();

        return $return;
    }

    /**
     * Get active quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }
}
