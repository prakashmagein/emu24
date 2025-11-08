<?php

namespace Swissup\Gdpr\Block;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;

class Js extends Template implements IdentityInterface
{
    protected $_template = 'Swissup_Gdpr::js.phtml';

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
        if (!$this->helper->isGdprEnabled()) {
            return '';
        }
        return $this->_template;
    }

    /**
     * @return mixed
     */
    public function getCookieSettingsJson()
    {
        if (!$this->helper->isCookieConsentEnabled()) {
            return false;
        }

        $groups = [];
        $cookies = [];
        foreach ($this->getGroups() as $group) {
            $code = $group->getCode();

            $groups[$code] = [
                'code' => $code,
                'required' => (int) $group->getRequired(),
                'prechecked' => (int) $group->getPrechecked(),
            ];

            foreach ($group->getCookies() as $cookie) {
                foreach ($cookie->getNames() as $name) {
                    $cookies[$name] = [
                        'name' => $name,
                        'group' => $code,
                    ];
                }
            }
        }

        $result = [
            'groups' => $groups,
            'cookies' => $cookies,
            'googleConsent' => (int) $this->helper->isGoogleConsentEnabled(),
            'lifetime' => (int) $this->helper->getCookieConsentLifetime(),
            'cookieName' => \Swissup\Gdpr\Model\CookieManager::COOKIE_NAME,
            'saveUrl' => $this->getUrl('swissup_gdpr/cookie/accept'),
            'registerUrl' => $this->getUrl('swissup_gdpr/cookie/unknown'),
        ];

        return $this->jsonEncoder->encode($result);
    }

    /**
     * @return array
     */
    private function getGroups()
    {
        return $this->groupRepository->getListWithCookies();
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        $result = [];

        foreach ($this->getGroups() as $group) {
            $result = array_merge($result, $group->getIdentities());
        }

        return array_unique($result);
    }
}
