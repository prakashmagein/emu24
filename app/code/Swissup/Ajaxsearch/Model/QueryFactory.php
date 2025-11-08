<?php
namespace Swissup\Ajaxsearch\Model;

use Magento\Search\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\StringUtils as StdlibString;

class QueryFactory extends \Magento\Search\Model\QueryFactory
{
    /**
     * Category variable
     */
    const CATEGORY_VAR_NAME = 'cat';

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var StdlibString
     */
    private $string;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Data
     */
    private $queryHelper;

    /**
     *
     * @var string
     */
    private $instanceName;

    /**
     * @var array of Query
     */
    private $queries;

    /**
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StdlibString $string
     * @param Data|null $queryHelper
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StdlibString $string,
        ?Data $queryHelper = null
    ) {
        parent::__construct($context, $objectManager, $string, $queryHelper);

        $this->request = $context->getRequest();
        $this->objectManager = $objectManager;
        $this->string = $string;
        $this->scopeConfig = $context->getScopeConfig();
        $this->queryHelper = $queryHelper === null ? $this->objectManager->get(Data::class) : $queryHelper;
    }

    /**
     *
     * @param string $instanceName
     */
    public function setInstanceName($instanceName)
    {
        $this->instanceName = $instanceName;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        if (!isset($this->queries[$this->instanceName])) {
            $maxQueryLength = $this->queryHelper->getMaxQueryLength();
            $minQueryLength = $this->queryHelper->getMinQueryLength();
            $rawQueryText = $this->getRawQueryText();
            $preparedQueryText = $this->getPreparedQueryText($rawQueryText, $maxQueryLength);
            $query = $this->create()->loadByQueryText($preparedQueryText);
            if (!$query->getId()) {
                $query->setQueryText($preparedQueryText);
            }
            $query->setIsQueryTextExceeded($this->isQueryTooLong($rawQueryText, $maxQueryLength));
            $query->setIsQueryTextShort($this->isQueryTooShort($rawQueryText, $minQueryLength));

            $categoryId = $this->getQueryCategoryId();
            if ($categoryId !== false) {
                $query->setQueryCategoryId($categoryId);
            }

            $this->queries[$this->instanceName] = $query;
        }
        return $this->queries[$this->instanceName];
    }

    /**
     * Create new instance
     *
     * @param array $data
     * @return \Magento\Search\Model\Query
     */
    public function create(array $data = [])
    {
        $instanceName = $this->instanceName;
        if (empty($instanceName)) {
            $instanceName = \Magento\Search\Model\Query::class;
        }
        return $this->objectManager->create($instanceName, $data);
    }

    /**
     * Retrieve search query text
     *
     * @return string
     */
    private function getRawQueryText()
    {
        $queryText = $this->request->getParam(self::QUERY_VAR_NAME);
        return ($queryText === null || is_array($queryText))
            ? ''
            : $this->string->cleanString(trim($queryText));
    }

    /**
     * @param string $queryText
     * @param int|string $maxQueryLength
     * @return string
     */
    private function getPreparedQueryText($queryText, $maxQueryLength)
    {
        if ($this->isQueryTooLong($queryText, $maxQueryLength)) {
            $queryText = $this->string->substr($queryText, 0, $maxQueryLength);
        }
        return $queryText;
    }

    /**
     * @param string $queryText
     * @param int|string $maxQueryLength
     * @return bool
     */
    private function isQueryTooLong($queryText, $maxQueryLength)
    {
        return ($maxQueryLength !== '' && $this->string->strlen($queryText) > $maxQueryLength);
    }

    /**
     * @param string $queryText
     * @param int|string $minQueryLength
     * @return bool
     */
    private function isQueryTooShort($queryText, $minQueryLength)
    {
        return ($this->string->strlen($queryText) < $minQueryLength);
    }

    /**
     * Retrieve search category param
     *
     * @return int|false
     */
    private function getQueryCategoryId()
    {
        $category = $this->request->getParam(self::CATEGORY_VAR_NAME);
        return ($category === null || is_array($category))
            ? false
            : (int) $this->string->cleanString(trim($category));
    }
}
