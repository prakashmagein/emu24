<?php
namespace Swissup\Askit\Model\Message\Source;

use Swissup\Askit\Model\Message;

class IsPrivate implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Swissup\Askit\Model\Message
     */
    protected $message;

    /**
     * Constructor
     *
     * @param \Swissup\Askit\Model\Message $message
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->message->getIsPrivateStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
