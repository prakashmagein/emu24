<?php

namespace Swissup\Askit\Ui\Component\Listing\MassAction;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\UrlInterface;

/**
 * Class Options
 */
class Status implements \JsonSerializable, OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * Additional options params
     *
     * @var array
     */
    protected $data;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var string[]
     */
    protected $statuses;

    /**
     * Base URL for subactions
     *
     * @var string
     */
    protected $urlPath;

    /**
     * Param name for subactions
     *
     * @var string
     */
    protected $paramName;

    /**
     * Additional params for subactions
     *
     * @var array
     */
    protected $additionalData = [];

    /**
     * Constructor
     *
     * @param UrlInterface $urlBuilder
     * @param \Swissup\Askit\Model\Message\Source\Status $modelStatus
     * @param array $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        \Swissup\Askit\Model\Message\Source\Status $modelStatus,
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->statuses = $modelStatus->getOptionArray();
        $this->data = $data;
    }

    /**
     * Get action options
     *
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $i = 0;
        if ($this->options === null) {
            $options = [];
            $statusses = $this->statuses;

            foreach ($statusses as $key => $label) {
                $options[$i]['value'] = $key;
                $options[$i]['label'] = $label;
                $i++;
            }

            $this->prepareData();
            foreach ($options as $optionCode) {
                $this->options[$optionCode['value']] = [
                    'type' => 'status_' . $optionCode['value'],
                    'label' => $optionCode['label'],
                ];

                if ($this->urlPath && $this->paramName) {
                    $this->options[$optionCode['value']]['url'] = $this->urlBuilder->getUrl(
                        $this->urlPath,
                        [$this->paramName => $optionCode['value'], '_current' => true]
                    );
                }

                $this->options[$optionCode['value']] = array_merge_recursive(
                    $this->options[$optionCode['value']],
                    $this->additionalData
                );
            }

            // return the massaction data
            $this->options = array_values($this->options);
        }
        return $this->options;
    }

    /**
     * Prepare addition data for subactions
     *
     * @return void
     */
    protected function prepareData()
    {

        foreach ($this->data as $key => $value) {
            switch ($key) {
                case 'urlPath':
                    $this->urlPath = $value;
                    break;
                case 'paramName':
                    $this->paramName = $value;
                    break;
                default:
                    $this->additionalData[$key] = $value;
                    break;
            }
        }
    }

    /**
     * @return string[]
     */
    public function toOptionArray()
    {
        return $this->statuses;
    }
}
