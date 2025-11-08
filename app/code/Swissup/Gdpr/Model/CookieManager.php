<?php

namespace Swissup\Gdpr\Model;

class CookieManager
{
    const COOKIE_NAME = 'cookie_consent';

    /**
     * Cookie name to group code mapping
     *
     * @var array
     */
    private $cookies = [];

    /**
     * @var array
     */
    private $allowedGroupCodes;

    /**
     * \Magento\Framework\Serialize\Serializer\Json
     */
    private $json;

    /**
     * \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $cookie;

    /**
     * \Swissup\Gdpr\Model\CookieGroupRepository
     */
    private $groupRepository;

    /**
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookie
     * @param \Swissup\Gdpr\Model\CookieGroupRepository $groupRepository
     */
    public function __construct(
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookie,
        \Swissup\Gdpr\Model\CookieGroupRepository $groupRepository
    ) {
        $this->json = $json;
        $this->cookie = $cookie;
        $this->groupRepository = $groupRepository;
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function isAllowed($name)
    {
        if (!$this->cookies) {
            $this->prepareData();
        }

        return in_array(
            $this->getGroupCodeByCookieName($name),
            $this->getAllowedGroupCodes()
        );
    }

    /**
     * Prepare cookie name to group code mapping
     */
    private function prepareData()
    {
        foreach ($this->groupRepository->getListWithCookies() as $group) {
            foreach ($group->getCookies() as $cookie) {
                foreach ($cookie->getNames() as $name) {
                    $this->cookies[$name] = $group->getCode();
                }
            }
        }
    }

    /**
     * @param string $name
     * @return string
     */
    private function getGroupCodeByCookieName($name)
    {
        if (isset($this->cookies[$name])) {
            return $this->cookies[$name];
        }

        // maybe we have cookie patterns, like 'ga_*'
        $patterns = array_keys($this->cookies);
        $patterns = array_filter($patterns, function ($name) {
            return strpos($name, '*') > 0;
        });
        $patterns = array_map(function ($name) {
            return str_replace('*', '', $name);
        }, $patterns);

        // move longest patterns to the top
        usort($patterns, function ($a, $b) {
            return strlen($b) - strlen($a);
        });

        foreach ($patterns as $prefix) {
            if (strpos($name, $prefix) !== 0 || strlen($name) <= strlen($prefix)) {
                continue;
            }

            if (isset($this->cookies[$prefix . '*'])) {
                return $this->cookies[$prefix . '*'];
            }
        }

        return false;
    }

    /**
     * @param $explicit When true - some required groups may not be returned
     * @return array
     */
    public function getAllowedGroupCodes($explicit = false)
    {
        if ($this->allowedGroupCodes !== null) {
            return $this->allowedGroupCodes;
        }

        $this->allowedGroupCodes = [];

        if (!$explicit) {
            foreach ($this->groupRepository->getListWithCookies() as $group) {
                if (!$group->getRequired()) {
                    continue;
                }
                $this->allowedGroupCodes[] = $group->getCode();
            }
        }

        $json = $this->cookie->getCookie(self::COOKIE_NAME);

        if ($json) {
            try {
                $data = $this->json->unserialize($json);
            } catch (\Exception $e) {
                $data = [];
            }

            $this->allowedGroupCodes = array_unique(array_merge(
                $this->allowedGroupCodes,
                $data['groups'] ?? []
            ));
        }

        return $this->allowedGroupCodes;
    }
}
