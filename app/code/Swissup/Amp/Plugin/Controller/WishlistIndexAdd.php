<?php
namespace Swissup\Amp\Plugin\Controller;

use Magento\Framework\App\ActionInterface;

class WishlistIndexAdd extends AbstractPlugin
{
    const SUCCESS_MESSAGE = '%1 has been added to your Wish List.';

    /**
     * Modify wishlist dispatch
     *
     * @param \Magento\Wishlist\Controller\Index\Add $subject
     * @param \Magento\Framework\App\RequestInterface $request
     * @return void
     */
    public function beforeDispatch(
        \Magento\Wishlist\Controller\Index\Add $subject,
        \Magento\Framework\App\RequestInterface $request
    ) {
        if ($request->isPost() && $request->getQuery('amp') &&
            $subject->getActionFlag()->get('', ActionInterface::FLAG_NO_DISPATCH)
        ) {
            $subject->getActionFlag()->set('', ActionInterface::FLAG_NO_DISPATCH, false);
        }
    }
}
