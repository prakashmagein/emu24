<?php

namespace Swissup\Easybanner\Model\Banner;

use Swissup\Easybanner\Model\Banner;
use Swissup\Easybanner\Model\BannerFactory;

class Copier
{
    /**
     * @var \Swissup\Easybanner\Model\BannerFactory
     */
    private $bannerFactory;

    public function __construct(BannerFactory $bannerFactory)
    {
        $this->bannerFactory = $bannerFactory;
    }

    public function copy(Banner $banner): Banner
    {
        $duplicate = $this->bannerFactory->create()
            ->setData($banner->getData())
            ->setIsDuplicate(true)
            ->setIdentifier($banner->getIdentifier())
            ->setId(null)
            ->setPlaceholders($banner->getPlaceholders())
            ->setStores($banner->getStores());

        $isDuplicateSaved = false;

        do {
            $identifier = $duplicate->getIdentifier();
            $identifier = preg_match('/(.*)-(\d+)$/', $identifier, $matches)
                ? $matches[1] . '-' . ($matches[2] + 1)
                : $identifier . '-1';
            $duplicate->setIdentifier($identifier);

            try {
                $duplicate->save();
                $isDuplicateSaved = true;
            } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
            }
        } while (!$isDuplicateSaved);

        return $duplicate;
    }
}
