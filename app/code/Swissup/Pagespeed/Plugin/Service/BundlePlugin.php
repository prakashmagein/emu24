<?php
namespace Swissup\Pagespeed\Plugin\Service;

use Magento\Framework\Locale\ResolverInterfaceFactory;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\DesignInterfaceFactory;
use Magento\Framework\View\Design\Theme\ListInterface;
use Magento\Framework\View\Asset\RepositoryFactory;

class BundlePlugin
{
    /**
     * @var ListInterface
     */
    private $themeList;

    /**
     * @var DesignInterfaceFactory
     */
    private $designFactory;

    /**
     * @var RepositoryFactory
     */
    private $assetRepoFactory;

    /**
     * @var ResolverInterfaceFactory
     */
    private $localeFactory;

    /**
     * @var \Swissup\Pagespeed\Model\BundleFactory
     */
    private $bundleServiceFactory;

    /**
     *
     * @param ListInterface $themeList
     * @param DesignInterfaceFactory $designFactory
     * @param RepositoryFactory $assetRepoFactory
     * @param ResolverInterfaceFactory $localeFactory
     * @param \Swissup\Pagespeed\Model\BundleFactory $bundleServiceFactory
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        ListInterface $themeList,
        DesignInterfaceFactory $designFactory,
        RepositoryFactory $assetRepoFactory,
        ResolverInterfaceFactory $localeFactory,
        \Swissup\Pagespeed\Model\BundleFactory $bundleServiceFactory
    ) {
        $this->themeList = $themeList;
        $this->designFactory = $designFactory;
        $this->assetRepoFactory = $assetRepoFactory;
        $this->localeFactory = $localeFactory;
        $this->bundleServiceFactory = $bundleServiceFactory;
    }

    /**
     *
     * @param  \Magento\Deploy\Service\Bundle $subject
     * @param  void $result
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDeploy(
        \Magento\Deploy\Service\Bundle $subject,
        $result,
        $areaCode,
        $themePath,
        $localeCode
    ) {
        /** @var \Magento\Framework\View\Design\ThemeInterface $theme */
        $theme = $this->themeList->getThemeByFullPath($areaCode . '/' . $themePath);
        /** @var \Magento\Theme\Model\View\Design $design */
        $design = $this->designFactory->create()->setDesignTheme($theme, $areaCode);
        /** @var ResolverInterface $locale */
        $locale = $this->localeFactory->create();
        $locale->setLocale($localeCode);
        $design->setLocale($locale);

        $assetRepo = $this->assetRepoFactory->create(['design' => $design]);

        $bundleService = $this->bundleServiceFactory->create([
            'staticContext' => $assetRepo->getStaticViewFileContext()
        ]);
        $bundleService->deploy();
    }
}
