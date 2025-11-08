<?php

namespace Swissup\SoldTogether\Model\Config\Backend;

class CronFrequency extends \Magento\Framework\App\Config\Value
{
    /**
     * Cron string path
     */
    const CRON_STRING_PATH = 'crontab/default/jobs/soldtogether_new_orders_process/schedule/cron_expr';

    /**
     * Cron mode path
     */
    const CRON_MODEL_PATH = 'crontab/default/jobs/soldtogether_new_orders_process/run/model';

    /**
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    protected $configValueFactory;

    /**
     * @var string
     */
    protected $runModelPath = '';

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        $runModelPath = '',
        array $data = []
    ) {
        $this->runModelPath = $runModelPath;
        $this->configValueFactory = $configValueFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * After save handler
     *
     * @return $this
     * @throws \Exception
     */
    public function afterSave()
    {
        $config = $this->configValueFactory->create();
        $config->load(self::CRON_STRING_PATH, 'path');
        $config->setPath(self::CRON_STRING_PATH);
        if ($this->getConfigValue('order_on_order_create')
            || $this->getConfigValue('customer_on_order_create')
        ) {
            $cronExprString = $this->getConfigValue('cron_frequency');
            $config->setValue($cronExprString);
        } else {
            $config->setValue('');
        }

        try {
            $config->save();

            $this->configValueFactory->create()
                ->load(self::CRON_MODEL_PATH, 'path')
                ->setPath(self::CRON_MODEL_PATH)
                ->setValue($this->runModelPath)
                ->save();
        } catch (\Exception $e) {
            throw new \Exception(__('We can\'t save the cron expression.'));
        }

        return parent::afterSave();
    }

    private function getConfigValue(
        string $field,
        string $group = 'relations'
    ): ?string {
        return $this->getData("groups/{$group}/fields/{$field}/value");
    }
}
