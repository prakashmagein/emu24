<?php
namespace Swissup\Amp\Plugin\Controller;

class CheckoutCartAdd extends AbstractPlugin
{
    const SUCCESS_MESSAGE = 'You added %1 to your shopping cart.';

    /**
     * @inheritdoc
     */
    protected function getRedirectTo($response)
    {
        $redirectTo = $this->helper->getRedirectTo($response);
        if (!$redirectTo && $this->helper->shouldRedirectToCart()) {
            $redirectTo = $this->helper->getCartUrl();
        }

        return $redirectTo;
    }
}
