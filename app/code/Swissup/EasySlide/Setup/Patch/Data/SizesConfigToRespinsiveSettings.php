<?php

namespace Swissup\EasySlide\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class SizesConfigToRespinsiveSettings implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * PatchInitial constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $sliderTable = $this->moduleDataSetup->getTable('swissup_easyslide_slider');
        if (!$this->moduleDataSetup->tableExists($sliderTable)) {
            return $this;
        }

        $select = $connection->select()
            ->from($sliderTable, ['slider_id', 'slider_config']);
        $data = $connection->fetchAssoc($select);
        if (!is_array($data)) {
            return $this;
        }

        foreach ($data as $sliderId => $row) {
            $config = json_decode($row['slider_config'], true);
            $sizes = $config['sizes']['sizes'] ?? [];
            if (!$sizes) {
                continue;
            }

            $responsiveSizes = [];
            $responsiveWidths = [];
            foreach ($sizes as $size) {
                $responsiveSizes[] = $size['media_query'] ?? false;
                $responsiveWidths[] = isset($size['image_width']) ? ($size['image_width'] . 'w') : false;
            }

            $responsiveSizes = array_filter($responsiveSizes);
            $responsiveWidths = array_filter($responsiveWidths);

            unset($config['sizes']);
            $config['responsive_widths'] = implode(', ', $responsiveWidths);
            $config['responsive_sizes'] = implode(', ', $responsiveSizes);
            $connection->update(
                $sliderTable,
                ['slider_config' => json_encode($config)],
                ['slider_id = ?' => $sliderId]
            );
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.8.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
