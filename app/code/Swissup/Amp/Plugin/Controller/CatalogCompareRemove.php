<?php
namespace Swissup\Amp\Plugin\Controller;

class CatalogCompareRemove extends AbstractPlugin
{
    const SUCCESS_MESSAGE = 'You removed product %1 from the comparison list.';

    /**
     * @inheritdoc
     */
    protected function getRedirectTo($response)
    {
        $redirectTo = $this->helper->getRedirectTo($response);
        if (!$redirectTo) {
            $redirectTo = $this->helper->getAmpUrl();
        }

        return $redirectTo;
    }
}
