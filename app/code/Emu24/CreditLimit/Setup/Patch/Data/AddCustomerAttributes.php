<?php
namespace Emu24\CreditLimit\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddCustomerAttributes implements DataPatchInterface
{
    private $moduleDataSetup;
    private $customerSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $attributes = [
            'regno' => [
                'type' => 'varchar',
                'label' => 'Registration Number',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'system' => 0,
                'position' => 1000,
                'adminhtml_only' => 1,
            ],
            'credit_limit' => [
                'type' => 'decimal',
                'label' => 'Credit Limit',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'system' => 0,
                'position' => 1001,
                'adminhtml_only' => 1,
            ],
        ];

        foreach ($attributes as $code => $data) {
            $customerSetup->addAttribute(Customer::ENTITY, $code, $data);
            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, $code);
            $attribute->setData('used_in_forms', ['adminhtml_customer']);
            $attribute->save();
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
