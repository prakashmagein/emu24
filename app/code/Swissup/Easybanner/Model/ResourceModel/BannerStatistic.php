<?php
namespace Swissup\Easybanner\Model\ResourceModel;

/**
 * BannerStatistic mysql resource
 */
class BannerStatistic extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const PERIOD_7_DAYS     = 1;
    const PERIOD_30_DAYS    = 2;
    const PERIOD_6_MONTHS   = 3;
    const PERIOD_12_MONTHS  = 4;
    const PERIOD_ALL_TIME   = 5;

    private $_date;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param string|null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_date = $date;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_easybanner_banner_statistic', 'id');
    }

    public function incrementDisplayCount($bannerId)
    {
        $todayDate = $this->_date->gmtDate('Y-m-d');
        $connection = $this->getConnection();
        $connection->insert($this->getMainTable(), [
            'banner_id' => $bannerId,
            'date' => $todayDate,
            'display_count' => 1
        ]);
    }

    public function incrementClicksCount($bannerId)
    {
        $todayDate = $this->_date->gmtDate('Y-m-d');
        $connection = $this->getConnection();
        $connection->insert($this->getMainTable(), [
            'banner_id' => $bannerId,
            'date' => $todayDate,
            'clicks_count' => 1
        ]);
    }

    public function getChartStatisticData($bannerId, $type)
    {
        switch ($type) {
            case self::PERIOD_7_DAYS:
                return $this->getDailyChartData($bannerId, 7);
            case self::PERIOD_30_DAYS:
                return $this->getDailyChartData($bannerId, 30);
            case self::PERIOD_6_MONTHS:
                return $this->getMonthlyChartData($bannerId, 6);
            default:
                return [];
        }
    }

    /**
     * @param  integer $bannerId
     * @param  integer $limit
     * @return array
     */
    private function getDailyChartData($bannerId, $limit = 7)
    {
        $data = [
            ['Date', /*'Display', */'Clicks']
        ];
        $connection = $this->getConnection();

        $from = date('Y-m-d', strtotime("-{$limit} days"));
        $select = $connection->select()
            ->from($this->getMainTable(), [
                'date' => 'date',
                // 'display_count' => 'SUM(display_count)',
                'clicks_count' => 'SUM(clicks_count)',
            ])
            ->where('banner_id = ?', $bannerId)
            ->having('date >= ?', $from)
            ->group('date');

        $result = $connection->fetchAssoc($select);

        for ($i = $limit; $i >= 0; $i--) {
            $key = date('Y-m-d', strtotime("-{$i} days"));
            if (array_key_exists($key, $result)) {
                $data[] = [
                    $key,
                    // $result[$key]['display_count'],
                    (int) $result[$key]['clicks_count']
                ];
            } else {
                $data[] = [$key, /*0, */0];
            }
        }
        return $data;
    }

    /**
     * @param  integer $bannerId
     * @param  integer $limit
     * @return array
     */
    private function getMonthlyChartData($bannerId, $limit = 6)
    {
        $data = [['Date', 'Clicks']];
        $connection = $this->getConnection();

        $select = $connection->select()
            ->from($this->getMainTable(), [
                'date' => 'SUBSTR(date, 1, 7)',
                'clicks_count' => 'SUM(clicks_count)',
            ])
            ->where('banner_id = ?', $bannerId)
            ->group('SUBSTR(date, 1, 7)')
            ->having('date >= ?', date('Y-m-d', strtotime("-{$limit} month")));

        $result = $connection->fetchAssoc($select);

        $date = new \DateTime();
        for ($i = 0; $i < $limit; $i++) {
            $key = $date->format('Y-m');
            $date->sub(new \DateInterval('P1M'));

            $coords = [$key, 0];

            if (array_key_exists($key, $result)) {
                $coords = [
                    $key,
                    (int) $result[$key]['clicks_count']
                ];
            }

            array_splice($data, 1, 0, [$coords]);
        }

        return $data;
    }

    /**
     * Condense banner statistics
     *
     * @param  string $date
     * @return $this
     */
    public function condenseStatistic($date)
    {
        // select condensed statistic
        $select = $this->getConnection()
            ->select()
            ->from(
                $this->getMainTable(),
                [
                    'banner_id',
                    'date',
                    'display_count' => new \Zend_Db_Expr('SUM(`display_count`)'),
                    'clicks_count' => new \Zend_Db_Expr('SUM(`clicks_count`)')
                ]
            )
            ->where('`date`=?', $date)
            ->group(['banner_id', 'date']);
        $statistic = $this->getConnection()->fetchAll($select);
        if ($statistic) {
            // delete all data for $date
            $this->getConnection()->delete(
                $this->getMainTable(),
                ['`date`=?' => $date]
            );
            // insert condensed statistic
            $this->getConnection()->insertMultiple(
                $this->getMainTable(),
                $statistic
            );
        }

        return $this;
    }

    /**
     * @param mixed $where
     * @return int Number of deleted rows
     */
    public function clear($where = null)
    {
        if ($where) {
            if (is_numeric($where)) {
                $where = ['banner_id = ?' => (int) $where];
            }
        } else {
            $where = '';
        }

        return $this->getConnection()->delete($this->getMainTable(), $where);
    }
}
