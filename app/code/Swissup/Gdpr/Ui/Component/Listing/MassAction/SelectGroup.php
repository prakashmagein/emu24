<?php

namespace Swissup\Gdpr\Ui\Component\Listing\MassAction;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\UrlInterface;

class SelectGroup implements \JsonSerializable, OptionSourceInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * Base URL for subactions
     *
     * @var string
     */
    private $urlPath;

    /**
     * Param name for subactions
     *
     * @var string
     */
    private $paramName;

    /**
     * Additional params for subactions
     *
     * @var array
     */
    private $additionalData = [];

    /**
     * @var \Swissup\Gdpr\Model\Config\Source\CookieGroup
     */
    private $cookieGroups;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * Additional options params
     *
     * @var array
     */
    private $data;

    /**
     * @param \Swissup\Gdpr\Model\Config\Source\CookieGroup $cookieGroups
     * @param UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        \Swissup\Gdpr\Model\Config\Source\CookieGroup $cookieGroups,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        $this->cookieGroups = $cookieGroups;
        $this->urlBuilder = $urlBuilder;
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
        if ($this->options !== null) {
            return $this->options;
        }

        $this->prepareData();

        foreach ($this->cookieGroups->toOptionArray() as $group) {
            $this->options[$group['value']] = [
                'type' => $group['value'],
                'label' => $group['label'],
            ];

            if ($this->urlPath && $this->paramName) {
                $this->options[$group['value']]['url'] = $this->urlBuilder->getUrl(
                    $this->urlPath,
                    [$this->paramName => $group['value']]
                );
            }

            $this->options[$group['value']] = array_merge_recursive(
                $this->options[$group['value']],
                $this->additionalData
            );
        }

        $this->options = array_values($this->options);

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

    public function toOptionArray()
    {
        return $this->cookieGroups->toOptionArray();
    }
}
