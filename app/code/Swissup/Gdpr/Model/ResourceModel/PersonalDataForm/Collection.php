<?php

namespace Swissup\Gdpr\Model\ResourceModel\PersonalDataForm;

use Magento\Framework\App\Area;

class Collection extends \Magento\Framework\Data\Collection
{
    /**
     * Item object class name
     *
     * @var string
     */
    protected $_itemObjectClass = \Swissup\Gdpr\Model\PersonalDataForm::class;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Swissup\Gdpr\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Email\Model\Template\Filter
     */
    private $filter;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    private array $forms = [];

    private array $formProviders = [];

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Swissup\Gdpr\Helper\Data $helper,
        \Magento\Email\Model\Template\FilterFactory $filterFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\State $appState,
        array $forms = [],
        array $formProviders = []
    ) {
        parent::__construct($entityFactory);

        $this->eventManager = $eventManager;
        $this->helper = $helper;
        $this->filter = $filterFactory->create();
        $this->storeManager = $storeManager;
        $this->appState = $appState;
        $this->forms = $forms;
        $this->formProviders = $formProviders;
    }

    /**
     * Add an object to the collection
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @throws LocalizedException
     */
    public function addItem(\Magento\Framework\DataObject $object)
    {
        if (!$object instanceof $this->_itemObjectClass) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Attempt to add an invalid object')
            );
        }
        return parent::addItem($object);
    }

    /**
     * Load data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        foreach ($this->formProviders as $provider) {
            foreach ($provider->getForms() as $form) {
                $this->forms[] = $form;
            }
        }

        foreach ($this->forms as $form) {
            if (is_array($form)) {
                $form = new \Swissup\Gdpr\Model\PersonalDataForm($form);
            }
            $this->addItem($form);
        }

        $this->eventManager->dispatch(
            'swissup_gdpr_personal_data_forms_load_before',
            ['collection' => $this]
        );

        $this->_setIsLoaded(true);

        $this->sortById()
            ->prepareJsConfig()
            ->addConsents();

        $this->eventManager->dispatch(
            'swissup_gdpr_personal_data_forms_load_after',
            ['collection' => $this]
        );

        $this->renderConsents()
            ->sortConsents();

        return $this;
    }

    /**
     * Sort items by ID
     *
     * @return $this
     */
    private function sortById()
    {
        $items = $this->getItems();

        uasort($items, function ($a, $b) {
            return $a->getId() > $b->getId() ? 1 : -1;
        });

        $this->_items = $items;

        return $this;
    }

    /**
     * Prepare js_config structure
     *
     * @return $this
     */
    private function prepareJsConfig()
    {
        foreach ($this->getItems() as $form) {
            $jsConfig = $form->getJsConfig();
            if (!$jsConfig) {
                $jsConfig = [];
            }

            if (empty($jsConfig['form']) && $form->getAction()) {
                $action = str_replace('_', '/', $form->getAction());
                $jsConfig['form'] = 'form[action*="' . $action . '"]';
            }

            if (empty($jsConfig['consents'])) {
                $jsConfig['consents'] = [];
            }

            $form->setJsConfig($jsConfig);
        }

        return $this;
    }

    /**
     * Add consents from configuration to each of the form
     *
     * @return $this
     */
    private function addConsents()
    {
        foreach ($this->helper->getConsents() as $id => $consent) {
            if (empty($consent['forms'])) {
                continue;
            }

            $formIds = explode(',', $consent['forms']);
            foreach ($formIds as $formId) {
                $form = $this->getItemById($formId);
                if (!$form) {
                    continue; // old config value
                }

                $consent['html_id'] = $id;
                $form->addConsent($consent);
            }
        }

        return $this;
    }

    /**
     * Render consent data with \Magento\Email\Model\Template\Filter
     *
     * @return $this
     */
    private function renderConsents()
    {
        if ($this->appState->getAreaCode() !== Area::AREA_FRONTEND) {
            return $this;
        }

        $storeId = $this->storeManager->getStore()->getId();

        foreach ($this->getItems() as $form) {
            $jsConfig = $form->getJsConfig();
            $consents = [];
            foreach ($form->getConsents() as $id => $consent) {
                $consents[$id] = $consent;
                $consents[$id]['title'] = $this->filter
                    ->setStoreId($storeId)
                    ->filter($consent['title']);

                if (!empty($consent['description'])) {
                    $consents[$id]['description'] = $this->filter
                        ->setStoreId($storeId)
                        ->filter($consent['description']);
                }
            }
            $jsConfig['consents'] = $consents;
            $form->setJsConfig($jsConfig);
        }

        return $this;
    }

    /**
     * Sort consents in each form
     *
     * @return $this
     */
    private function sortConsents()
    {
        foreach ($this->getItems() as $form) {
            $jsConfig = $form->getJsConfig();
            if (empty($jsConfig['consents'])) {
                continue;
            }

            usort($jsConfig['consents'], function ($a, $b) {
                return $a['sort_order'] > $b['sort_order'] ? 1 : -1;
            });
            $form->setJsConfig($jsConfig);
        }

        return $this;
    }
}
