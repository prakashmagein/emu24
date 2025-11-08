<?php
namespace Swissup\Amp\Plugin\Framework;

class ViewFileSystem
{
    /**
     * @var \Swissup\Amp\Helper\Data
     */
    protected $helper;

    /**
     * @param \Swissup\Amp\Helper\Data $helper
     */
    public function __construct(
        \Swissup\Amp\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Get a template file
     *
     * @param \Magento\Framework\View\FileSystem $subject
     * @param string $fileId
     * @param array $params
     * @return array
     */
    public function beforeGetTemplateFileName(
        \Magento\Framework\View\FileSystem $subject,
        $fileId,
        array $params = []
    ) {
        if ($this->helper->canUseAmp() && $fileId == 'Magento_Theme::root.phtml') {
            $fileId = 'Swissup_Amp::root.phtml';
        }

        return [$fileId, $params];
    }
}
