<?php

namespace Swissup\SoldTogetherImportExport\Model\LinkDataMerger;

abstract class AbstractMerger
{
    /**
     * @var array
     */
    protected $map = [];

    /**
     * @param array $mergeMap
     */
    public function __construct(array $mergeMap = [])
    {
        $this->map = $mergeMap;
    }

    /**
     * @param  array $dbRow
     * @param  array $importRow
     * @return array
     */
    public function merge(array $dbRow, array $importRow): array
    {
        $newRow = [];
        foreach ($this->map as $mapItem) {
            if (is_array($mapItem)) {
                $importColumn = array_key_first($mapItem);
                $dbColumn = reset($mapItem);
            } else {
                $importColumn = $mapItem;
                $dbColumn = $mapItem;
            }

            if (strpos($dbColumn, '/') !== false) {
                list($columnName, $childName) = explode('/', $dbColumn);
                $newRow[$columnName][$childName] = $dbRow[$columnName][$childName] ?? '';
                $column = &$newRow[$columnName][$childName];
            } else {
                $newRow[$dbColumn] = $dbRow[$dbColumn] ?? '';
                $column = &$newRow[$dbColumn];
            }

            if (isset($importRow[$importColumn])) {
                $column = $importRow[$importColumn];
            }
        }

        $newRow = array_map(function ($value) {
            return is_array($value) ? array_filter($value) : $value;
        }, $newRow);

        return $newRow;
    }
}
