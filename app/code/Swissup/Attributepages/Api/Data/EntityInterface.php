<?php
namespace Swissup\Attributepages\Api\Data;

interface EntityInterface
{
    const ENTITY_ID = 'entity_id';
    const ATTRIBUTE_ID = 'attribute_id';
    const OPTION_ID = 'option_id';
    const NAME = 'name';
    const IDENTIFIER = 'identifier';
    const TITLE = 'title';
    const PAGE_TITLE = 'page_title';
    const CONTENT = 'content';
    const IMAGE = 'image';
    const THUMBNAIL = 'thumbnail';
    const META_TITLE = 'meta_title';
    const META_KEYWORDS = 'meta_keywords';
    const META_DESCRIPTION = 'meta_description';
    const DISPLAY_SETTINGS = 'display_settings';
    const DISPLAY_MODE = 'display_mode';
    const ROOT_TEMPLATE = 'root_template';
    const LAYOUT_UPDATE_XML = 'layout_update_xml';
    const USE_FOR_ATTRIBUTE_PAGE = 'use_for_attribute_page';
    const USE_FOR_PRODUCT_PAGE = 'use_for_product_page';
    const EXCLUDED_OPTION_IDS = 'excluded_option_ids';

    /**
     * Get entity_id
     *
     * return int
     */
    public function getEntityId();

    /**
     * Get attribute_id
     *
     * return int
     */
    public function getAttributeId();

    /**
     * Get option_id
     *
     * return int
     */
    public function getOptionId();

    /**
     * Get name
     *
     * return string
     */
    public function getName();

    /**
     * Get identifier
     *
     * return string
     */
    public function getIdentifier();

    /**
     * Get title
     *
     * return string
     */
    public function getTitle();

    /**
     * Get page_title
     *
     * return string
     */
    public function getPageTitle();

    /**
     * Get content
     *
     * return string
     */
    public function getContent();

    /**
     * Get image
     *
     * return string
     */
    public function getImage();

    /**
     * Get thumbnail
     *
     * return string
     */
    public function getThumbnail();

    /**
     * Get meta_title
     *
     * return string
     */
    public function getMetaTitle();

    /**
     * Get meta_keywords
     *
     * return string
     */
    public function getMetaKeywords();

    /**
     * Get meta_description
     *
     * return string
     */
    public function getMetaDescription();

    /**
     * Get display_settings
     *
     * return string
     */
    public function getDisplaySettings();

    /**
     * Get root_template
     *
     * return string
     */
    public function getRootTemplate();

    /**
     * Get layout_update_xml
     *
     * return string
     */
    public function getLayoutUpdateXml();

    /**
     * Get use_for_attribute_page
     *
     * return int
     */
    public function getUseForAttributePage();

    /**
     * Get use_for_product_page
     *
     * return int
     */
    public function getUseForProductPage();

    /**
     * Get excluded_option_ids
     *
     * return string
     */
    public function getExcludedOptionIds();


    /**
     * Set entity_id
     *
     * @param int $entityId
     * return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setEntityId($entityId);

    /**
     * Set attribute_id
     *
     * @param int $attributeId
     * return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setAttributeId($attributeId);

    /**
     * Set option_id
     *
     * @param int $optionId
     * return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setOptionId($optionId);

    /**
     * Set name
     *
     * @param string $name
     * return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setName($name);

    /**
     * Set identifier
     *
     * @param string $identifier
     * return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setIdentifier($identifier);

    /**
     * Set title
     *
     * @param string $title
     * return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setTitle($title);

    /**
     * Set page_title
     *
     * @param string $pageTitle
     * return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setPageTitle($pageTitle);

    /**
     * Set content
     *
     * @param string $content
     * return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setContent($content);

    /**
     * Set image
     *
     * @param string $image
     * return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setImage($image);

    /**
     * Set thumbnail
     *
     * @param string $thumbnail
     * return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setThumbnail($thumbnail);

    /**
     * Set meta_keywords
     *
     * @param string $metaKeywords
     * return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setMetaKeywords($metaKeywords);

    /**
     * Set meta_description
     *
     * @param string $metaDescription
     * return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setMetaDescription($metaDescription);

    /**
     * Set display_settings
     *
     * @param string $displaySettings
     * return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setDisplaySettings($displaySettings);

    /**
     * Set root_template
     *
     * @param string $rootTemplate
     * return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setRootTemplate($rootTemplate);

    /**
     * Set layout_update_xml
     *
     * @param string $layoutUpdateXml
     * return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setLayoutUpdateXml($layoutUpdateXml);

    /**
     * Set use_for_attribute_page
     *
     * @param int $useForAttributePage
     * return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setUseForAttributePage($useForAttributePage);

    /**
     * Set use_for_product_page
     *
     * @param int $useForProductPage
     * return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setUseForProductPage($useForProductPage);

    /**
     * Set excluded_option_ids
     *
     * @param string $excludedOptionIds
     * return \Swissup\Attributepages\Api\Data\EntityInterface
     */
    public function setExcludedOptionIds($excludedOptionIds);
}
