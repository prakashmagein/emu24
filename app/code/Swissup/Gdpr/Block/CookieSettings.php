<?php

namespace Swissup\Gdpr\Block;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class CookieSettings extends Template implements BlockInterface
{
    protected $_template = 'Swissup_Gdpr::cookie-settings.phtml';

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Swissup\Gdpr\Model\CookieGroupRepository
     */
    private $groupRepository;

    /**
     * @var \Swissup\Gdpr\Helper\Data
     */
    private $helper;

    /**
     * @param Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Swissup\Gdpr\Model\CookieGroupRepository $groupRepository
     * @param \Swissup\Gdpr\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Swissup\Gdpr\Model\CookieGroupRepository $groupRepository,
        \Swissup\Gdpr\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->jsonEncoder = $jsonEncoder;
        $this->groupRepository = $groupRepository;
        $this->helper = $helper;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        if (!$this->helper->isCookieConsentEnabled()) {
            return '';
        }
        return $this->_template;
    }

    public function getCssClass()
    {
        $classes = [
            'cookie-settings',
            'cookie-settings-cols' . $this->getColumnsCount(),
        ];

        if ($this->getColumnsCount() > 1) {
            $classes[] = 'cookie-settings-multicols';
        }

        return implode(' ', $classes);
    }

    public function getDescriptionHtml()
    {
        if ($this->getHideDescription()) {
            return '';
        }
        return $this->helper->getCookieSettingsText();
    }

    public function getColumnsCount()
    {
        $result = $this->getData('columns_count');

        if (!$result) {
            $result = $this->helper->getCookieSettingsColumnsCount();
        }

        if (!is_numeric($result)) {
            $groupsCount = count($this->getGroups());

            if ($groupsCount % 3 === 0) {
                $result = 3;
            } else {
                $result = 2;
            }
        }

        return $result;
    }

    private function getGroups()
    {
        return $this->groupRepository->getListWithCookies();
    }

    public function getGroupedCookies()
    {
        $result = [];

        foreach ($this->getGroups() as $group) {
            $code = $group->getCode();

            $result[$code] = [
                'code' => $code,
                'title' => __($group->getTitle()),
                'description' => __($group->getDescription()),
                'cookies' => [],
            ];

            foreach ($group->getCookies() as $cookie) {
                $result[$code]['cookies'][] = [
                    'name' => $cookie->getName(),
                    'description' => __($cookie->getDescription()),
                ];
            }
        }

        return array_values($result);
    }

    public function getGroupedCookiesJson()
    {
        return $this->jsonEncoder->encode($this->getGroupedCookies());
    }
}
