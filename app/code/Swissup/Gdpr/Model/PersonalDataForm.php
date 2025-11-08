<?php

namespace Swissup\Gdpr\Model;

class PersonalDataForm extends \Magento\Framework\DataObject
{
    /**
     * Add consent
     *
     *  Required keys:
     *      html_id
     *      enabled
     *      sort_order
     *      title
     *
     * @param array $consent
     */
    public function addConsent(array $consent)
    {
        $jsConfig = $this->getJsConfig();
        if (!isset($jsConfig['consents'])) {
            $jsConfig['consents'] = [];
        }

        $jsConfig['consents'][] = $consent;
        $this->setJsConfig($jsConfig);
        return $this;
    }

    /**
     * Get consents list
     *
     * @param  boolean $activeOnly
     * @return array
     */
    public function getConsents($activeOnly = true)
    {
        $jsConfig = $this->getJsConfig();
        if (!$jsConfig || empty($jsConfig['consents'])) {
            return [];
        }

        $result = [];
        foreach ($jsConfig['consents'] as $consent) {
            if ($activeOnly && empty($consent['enabled'])) {
                continue;
            }
            $result[] = $consent;
        }
        return $result;
    }

    /**
     * Get the form shortname (Without module name)
     *
     * @return string
     */
    public function getShortname()
    {
        if ($this->hasData('shortname')) {
            return $this->getData('shortname');
        }

        // try to return name without prefix: "Askit:", "Magento:", etc.
        $name = $this->getName();
        if (strpos($name, ':') !== false) {
            $parts = explode(':', $name, 2);
            return trim($parts[1]);
        }

        return $name;
    }
}
