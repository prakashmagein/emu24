<?php
namespace Swissup\Amp\Plugin\Controller;

class ContactIndexPost extends AbstractPlugin
{
    const SUCCESS_MESSAGE = 'Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.';

    /**
     * @inheritdoc
     */
    protected function getSuccessMessage($request)
    {
        return __(self::SUCCESS_MESSAGE);
    }
}
