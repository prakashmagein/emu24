<?php
namespace Swissup\Askit\Model\ResourceModel\Answer\Grid;

use Swissup\Askit\Model\ResourceModel\Answer\Collection as AnswerCollection;

use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Psr\Log\LoggerInterface as Logger;

class Collection extends AnswerCollection
{
    /**
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable,
        $resourceModel
    ) {
        $this->_init(\Magento\Framework\View\Element\UiComponent\DataProvider\Document::class, $resourceModel);
        $this->setMainTable(true);
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            null,
            null
        );
        $this->setMainTable($this->_resource->getTable($mainTable));
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * Join questions data to select.
     *
     * @param  array  $columns
     * @return $this
     */
    public function joinQuestionData($columns = [])
    {
        $table = $this->getTable('swissup_askit_message');
        $this->getSelect()->joinLeft(
            ['q' => $table],
            'q.id = main_table.parent_id',
            $columns
        );

        $this->addFilterToMap('question_text', 'q.text');

        return $this;
    }

    /**
     *
     * @return void
     */
    protected function _construct()
    {
//        $this->_init(
//            \Swissup\Askit\Model\Message::class,
//            \Swissup\Askit\Model\ResourceModel\Message::class
//        );
    }

    /**
     * Prepare select for load
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->joinItemTable();
        return $this;
    }
}
