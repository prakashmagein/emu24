<?php

namespace Swissup\Easybanner\Installer\Command;

class Banner
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Swissup\Easybanner\Model\BannerFactory
     */
    private $bannerFactory;

    /**
     * @var \Swissup\Easybanner\Model\PlaceholderFactory
     */
    private $placeholderFactory;

    /**
     * @var \Swissup\Easybanner\Model\ResourceModel\Banner\CollectionFactory
     */
    private $bannerCollectionFactory;

    /**
     * @var \Swissup\Easybanner\Model\ResourceModel\Placeholder\CollectionFactory
     */
    private $placeholderCollectionFactory;

    /**
     * @param \Swissup\Easybanner\Model\BannerFactory $bannerFactory
     * @param \Swissup\Easybanner\Model\PlaceholderFactory $placeholderFactory
     * @param \Swissup\Easybanner\Model\ResourceModel\Banner\CollectionFactory $bannerCollectionFactory
     * @param \Swissup\Easybanner\Model\ResourceModel\Placeholder\CollectionFactory $placeholderCollectionFactory
     */
    public function __construct(
        \Swissup\Easybanner\Model\BannerFactory $bannerFactory,
        \Swissup\Easybanner\Model\PlaceholderFactory $placeholderFactory,
        \Swissup\Easybanner\Model\ResourceModel\Banner\CollectionFactory $bannerCollectionFactory,
        \Swissup\Easybanner\Model\ResourceModel\Placeholder\CollectionFactory $placeholderCollectionFactory
    ) {
        $this->bannerFactory = $bannerFactory;
        $this->placeholderFactory = $placeholderFactory;
        $this->bannerCollectionFactory = $bannerCollectionFactory;
        $this->placeholderCollectionFactory = $placeholderCollectionFactory;
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * Create new placeholders and banners.
     * If duplicate is found - do nothing.
     *
     * @param \Swissup\Marketplace\Installer\Request $request
     */
    public function execute($request)
    {
        $this->logger->info('Easybanner: Create placeholders and banners');

        // 1. prepare collection of existing placeholders
        $placeholderNames = [];
        foreach ($request->getParams() as $data) {
            if (!empty($data['name'])) {
                $placeholderNames[] = $data['name'];
            }
        }

        $placeholders = $this->placeholderCollectionFactory
            ->create()
            ->addFieldToFilter('name', ['in' => $placeholderNames]);

        // 2. prepare collection of existing banners
        $bannerIdentifiers = [];
        foreach ($request->getParams() as $data) {
            if (empty($data['banners'])) {
                continue;
            }

            foreach ($data['banners'] as $banner) {
                $bannerIdentifiers[] = $banner['identifier'];
            }
        }

        $banners = $this->bannerCollectionFactory
            ->create()
            ->addFieldToFilter('identifier', ['in' => $bannerIdentifiers])
            ->addStoreFilter($request->getStoreIds(), false);

        // 3. create new placeholders and banners
        $placeholderDefaults = [
            'status' => 1,
            'limit' => 1,
            'mode' => 'rotator',
            'banner_offset' => 1,
            'sort_mode' => 'sort_order',
        ];
        $bannerDefaults = [
            'type' => 1,
            'sort_order' => 10,
            'html' => '',
            'status' => 1,
            'mode' => 'image',
            'target' => 'popup',
            'hide_url' => 0,
            'resize_image' => 0,
            'retina_support' => 0,
            'width' => 0,
            'height' => 0,
        ];

        foreach ($request->getParams() as $placeholderData) {
            $placeholder = $placeholders->getItemByColumnValue(
                'name',
                $placeholderData['name']
            );

            if (!$placeholder) {
                $placeholder = $this->placeholderFactory->create();

                try {
                    $placeholder
                        ->setData(array_merge($placeholderDefaults, $placeholderData))
                        ->save();
                } catch (\Exception $e) {
                    $this->logger->warning($e->getMessage());
                    continue;
                }
            }

            if (empty($placeholderData['banners'])) {
                continue;
            }

            $bannerDefaults['sort_order'] = 10;
            foreach ($placeholderData['banners'] as $bannerData) {
                if (!empty($bannerData['sort_order'])) {
                    $bannerDefaults['sort_order'] = $bannerData['sort_order'];
                }

                // we will use existing banner, if it is linked to our placeholder
                $collection = $banners->getItemsByColumnValue(
                    'identifier',
                    $bannerData['identifier']
                );

                foreach ($collection as $banner) {
                    // load store ids and placeholders
                    $banner->load($banner->getId());

                    if (in_array($placeholder->getId(), $banner->getPlaceholders())) {
                        continue 2;
                    }
                }

                // create banner if needed
                $banner = $this->bannerFactory->create()
                    ->setData(array_merge($bannerDefaults, $bannerData))
                    ->setStores($request->getStoreIds())
                    ->setPlaceholders([$placeholder->getId()]);

                try {
                    $banner->save();
                } catch (\Exception $e) {
                    $this->logger->warning($e->getMessage());
                }

                $bannerDefaults['sort_order'] += 5;
            }
        }
    }
}
