<?php
declare(strict_types=1);

namespace Swissup\ChatGptAssistant\Block\Adminhtml\Product\Edit\Action\Attribute\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Catalog\Block\Adminhtml\Form;
use Magento\Catalog\Helper\Product\Edit\Action\Attribute;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Swissup\ChatGptAssistant\Model\ResourceModel\Prompt\CollectionFactory as PromptCollectionFactory;

class Attributes extends Form implements TabInterface
{
    /*
     * Fields allowed for AI content generation
     */
    const ALLOWED_FIELDS = ['description', 'short_description', 'meta_description', 'meta_keyword', 'meta_title'];

    protected ProductFactory $productFactory;

    protected Attribute $attributeAction;

    private array $excludeFields;

    private SecureHtmlRenderer $secureRenderer;

    private PromptCollectionFactory $promptCollectionFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        ProductFactory $productFactory,
        Attribute $attributeAction,
        PromptCollectionFactory $promptCollectionFactory,
        array $data = [],
        ?array $excludeFields = null,
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->attributeAction = $attributeAction;
        $this->productFactory = $productFactory;
        $this->excludeFields = $excludeFields ?: [];
        $this->promptCollectionFactory = $promptCollectionFactory;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->secureRenderer = $secureRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
    }

    /**
     * Prepares form
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm(): void
    {
        $attributes = $this->getAttributes();
        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            if (!in_array($attributeCode, self::ALLOWED_FIELDS)) {
                $this->excludeFields[] = $attributeCode;
            }
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset('fields', ['legend' => __('Select fields and prompts to generate content with AI')]);
        $form->setDataObject($this->productFactory->create());
        $this->_setFieldset($attributes, $fieldset, $this->excludeFields);
        $form->setFieldNameSuffix('attributes');
        $this->setForm($form);
    }

    /**
     * Retrieve attributes for product mass update
     *
     * @return \Magento\Framework\DataObject[]
     */
    public function getAttributes()
    {
        return $this->attributeAction->getAttributes()->getItems();
    }

    /**
     * Custom additional element html
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getAdditionalElementHtml($element)
    {
        $elementId = $element->getId();
        $dataAttribute = "data-disable='{$elementId}'";
        $dataCheckboxName = "toggle_{$elementId}";
        $checkboxLabel = __('Generate');
        $promptSelectName = "prompt_{$elementId}";
        $promptsHtml = $this->getFieldPromptsHtml($elementId);
        // @codingStandardsIgnoreStart
        $html = <<<HTML
<span class="prompt-select">
    <select class="admin__control-select" id="$promptSelectName" name="attributes[$promptSelectName]" disabled>
        {$promptsHtml}
    </select>
</span>
<span class="attribute-change-checkbox">
    <input type="checkbox" id="$dataCheckboxName" name="$dataCheckboxName"
           class="checkbox" $dataAttribute />
    <label class="label" for="$dataCheckboxName">
        {$checkboxLabel}
    </label>
</span>
HTML;

        $html .= /* @noEscape */ $this->secureRenderer->renderEventListenerAsTag(
            'onclick',
            "toogleFieldEditMode(this, '{$promptSelectName}')",
            "#". $dataCheckboxName
        );

        // @codingStandardsIgnoreEnd
        return $html;
    }

    /**
     * Get HTML with field prompts
     *
     * @param string $elementId
     * @return string
     */
    protected function getFieldPromptsHtml($elementId)
    {
        $fieldId = 'product-' . str_replace('_', '-', $elementId);
        $prompts = $this->promptCollectionFactory->create()
            ->addStatusFilter(
                \Swissup\ChatGptAssistant\Model\Prompt::STATUS_ENABLED
            )
            ->addFieldToFilter('field_ids', ['finset' => $fieldId]);

        $html = '';
        foreach ($prompts as $prompt) {
            $selected = $prompt->getDefaultFieldId() == $fieldId ? ' selected' : '';
            $html .= "<option value='{$prompt->getId()}' $selected>{$prompt->getName()}</option>";
        }

        return $html;
    }

    /**
     * Returns tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Attributes');
    }

    /**
     * Return Tab title
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Attributes');
    }

    /**
     * Can show tab in tabs
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab not hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
