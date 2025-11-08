<?php

namespace Swissup\Gdpr\Model\PersonalDataHandler;

use Swissup\Gdpr\Model\ClientRequest;

class AbstractHandler
{
    /**
     * @var \Swissup\Gdpr\Helper\Data
     */
    protected $helper;

    /**
     * @var \Swissup\Gdpr\Model\Faker
     */
    protected $faker;

    /**
     * @var \Magento\Customer\Model\Config\Share
     */
    protected $shareConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Swissup\Gdpr\Model\PersonalDataHandler\Context $context
     */
    public function __construct(
        \Swissup\Gdpr\Model\PersonalDataHandler\Context $context
    ) {
        $this->helper = $context->getHelper();
        $this->faker = $context->getFaker();
        $this->shareConfig = $context->getShareConfig();
        $this->storeManager = $context->getStoreManager();
    }

    /**
     * @return void
     */
    public function beforeDelete(ClientRequest $request)
    {
        return true;
    }

    /**
     * @return boolean
     */
    public function useWebsiteFilter()
    {
        return $this->shareConfig->isWebsiteScope() &&
            count($this->storeManager->getWebsites()) > 1;
    }

    /**
     * @param  ClientRequest $request
     * @return array
     */
    public function getStoreIds(ClientRequest $request)
    {
        return $this->storeManager
            ->getWebsite($request->getWebsiteId())
            ->getStoreIds();
    }

    /**
     * @param  array   $collections
     * @param  array   $data
     * @param  boolean $useDbQuery
     * @return void
     */
    public function anonymizeCollections($collections, $data, $useDbQuery = false)
    {
        foreach ($collections as $collection) {
            foreach ($collection as $item) {
                $row = [];
                foreach ($data as $key => $value) {
                    if (!$item->getData($key)) {
                        continue;
                    }
                    $row[$key] = $value;
                }

                if (!$useDbQuery) {
                    $item->addData($row)->save();
                } else {
                    $connection = $collection->getConnection();
                    $table = $collection->getMainTable();

                    // filter out all keys that are not found in the table
                    $tableInfo = $connection->describeTable($table);
                    $row = array_intersect_key($row, array_flip(array_keys($tableInfo)));
                    if (!$row) {
                        return;
                    }

                    // get primary column name
                    $idFieldName = $collection->getIdFieldName();
                    if (!$idFieldName) {
                        foreach ($tableInfo as $column => $info) {
                            if ($info['PRIMARY']) {
                                $idFieldName = $column;
                                break;
                            }
                        }
                    }

                    $connection->update(
                        $table,
                        $row,
                        $connection->quoteInto($idFieldName . ' = ?', $item->getId())
                    );
                }
            }
        }
    }
}
