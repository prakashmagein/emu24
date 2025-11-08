<?php
declare(strict_types=1);

namespace Swissup\ImageOptimizer\Model\View;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GetCatalogMediaEntities
{

    /**
     * @var \Magento\Framework\View\ConfigInterface
     */
    private $viewConfig;

    /**
     * @var \Magento\Theme\Model\Config\Customization
     */
    private $themeCustomizationConfig;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory
     */
    private $themeCollectionFactory;

    /**
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonSerializer;

    /**
     * @param \Magento\Framework\View\ConfigInterface $viewConfig
     * @param \Magento\Theme\Model\Config\Customization $themeCustomizationConfig
     * @param \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $themeCollectionFactory
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
     */
    public function __construct(
        \Magento\Framework\View\ConfigInterface $viewConfig,
        \Magento\Theme\Model\Config\Customization $themeCustomizationConfig,
        \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $themeCollectionFactory,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer

    ) {
        $this->viewConfig = $viewConfig;
        $this->themeCustomizationConfig = $themeCustomizationConfig;
        $this->themeCollectionFactory = $themeCollectionFactory;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Search the current theme
     * @return array
     */
    private function getThemes(): array
    {
        $themes = [];
        $registeredThemes = $this->themeCollectionFactory->create()
            ->loadRegisteredThemes();
        $storesByThemes = $this->themeCustomizationConfig->getStoresByThemes();
        $keyType = is_integer(key($storesByThemes)) ? 'getId' : 'getCode';
        foreach ($registeredThemes as $registeredTheme) {
            if (array_key_exists($registeredTheme->$keyType(), $storesByThemes)) {
                $themes[] = $registeredTheme;
            }
        }
        return $themes;
    }

    /**
     * Get unique image index
     * @param array $imageData
     * @return string
     */
    private function getUniqueImageIndex(array $imageData): string
    {
        ksort($imageData);
        unset($imageData['type']);
        return hash('md5', $this->jsonSerializer->serialize($imageData));
    }

    /**
     * @return string[]
     */
    private function getImportanIds()
    {
        return [
            'category_page_grid',
            'category_page_list',
            'product_base_image',
//            'product_page_image_large',
            'product_page_image_medium',
            'product_page_image_small',
//            'product_swatch_image_large',
//            'product_swatch_image_medium',
//            'product_swatch_image_small',
//            'product_page_main_image',
//            'product_page_more_views',
            'product_small_image',
            'product_thumbnail_image',
//            'wishlist_small_image',
//            'wishlist_thumbnail',
//            'new_products_content_widget_grid',
//            'new_products_content_widget_list',
        ];
    }

    /**
     * Get view images data from themes
     * @param array $themes
     * @return array
     */
    private function getViewImages(array $themes): array
    {
        $viewImages = [];
        /** @var \Magento\Theme\Model\Theme $theme */
        foreach ($themes as $theme) {
            $config = $this->viewConfig->getViewConfig([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'themeModel' => $theme,
            ]);
            $entity = $config->getMediaEntities(
                'Magento_Catalog',
                \Magento\Catalog\Helper\Image::MEDIA_TYPE_CONFIG_NODE
            );
            $importantImageTypeIds = $this->getImportanIds();
            foreach ($entity as $id => $configurationData) {
                if (!in_array($id, $importantImageTypeIds)) {
                    continue;
                }
                $uniqIndex = $this->getUniqueImageIndex($configurationData);
                $configurationData['id'] = $id;
                $viewImages[$uniqIndex] = $configurationData;
            }
        }

        return $viewImages;
    }

    /**
     * @param array $themes
     * @return array
     */
    public function get(array $themes): array
    {
        if (empty($themes)) {
            $themes = $this->getThemes();
        }

        return $this->getViewImages($themes);
    }
}
