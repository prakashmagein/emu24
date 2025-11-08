<?php

namespace Swissup\Gdpr\Ui\DataProvider\Form;

use Magento\Framework\App\RequestInterface;

class AbstractProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param RequestInterface $request
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        $this->request = $request;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Prepare meta data
     *
     * @param array $meta
     * @return array
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        if (!$this->request->getParam('store')) {
            return $meta;
        }

        foreach ($this->getScopeSpecificFields() as $fieldset => $fields) {
            foreach ($fields as $field) {
                $meta[$fieldset]['children'][$field]['arguments']['data']['config']['service'] = [
                    'template' => 'ui/form/element/helper/service',
                ];
                $meta[$fieldset]['children'][$field]['arguments']['data']['config']['disabled'] =
                    !$this->isScopeOverriddenValue($field);
            }
        }

        foreach ($this->getNonScopeSpecificFields() as $fieldset => $fields) {
            foreach ($fields as $field) {
                $meta[$fieldset]['children'][$field]['arguments']['data']['config']['visible'] = false;
            }
        }

        return $meta;
    }

    protected function getScopeSpecificFields()
    {
        return [];
    }

    protected function getNonScopeSpecificFields()
    {
        return [];
    }

    protected function isScopeOverriddenValue($field)
    {
        $data = $this->getData();
        if (!$data) {
            return false;
        }

        $data = current($data);

        if (empty($data['store_id'])) {
            return false; // all values are from default store view
        }

        return isset($data['content']['scope'][$field]);
    }
}
