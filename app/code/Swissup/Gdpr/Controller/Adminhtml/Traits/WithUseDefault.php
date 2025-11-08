<?php

namespace Swissup\Gdpr\Controller\Adminhtml\Traits;

trait WithUseDefault
{
    protected function processUseDefault(array $data)
    {
        $useDefault = $this->getRequest()->getParam('use_default', []);
        foreach ($useDefault as $key => $flag) {
            $flag = (int) $flag;
            if (!$flag) {
                continue;
            }

            if (isset($data[$key])) {
                $data[$key] = null;
            }
        }
        return $data;
    }
}
