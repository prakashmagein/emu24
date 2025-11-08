<?php
namespace Swissup\Pagespeed\Plugin\Model\Bundle;

use Magento\Deploy\Package\Package;

class ManagerPlugin
{
    /**
     * Supported content types.
     *
     * @var array
     */
    private const CONTENT_TYPES = ["js"];

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * @var \Magento\Csp\Model\SubresourceIntegrityFactory|null
     */
    private $integrityFactory;

    /**
     * @var \Magento\Csp\Model\SubresourceIntegrityRepositoryPool|null
     */
    private $integrityRepositoryPool;

    /**
     * @var \Magento\Csp\Model\SubresourceIntegrityCollector|null
     */
    private $integrityCollector;

    /**
     * @var \Magento\Csp\Model\SubresourceIntegrity\HashGenerator|null
     */
    private $hashGenerator;

    private $integrityRepositories = [];

    private $integrityStatus = [];

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $integrityFactoryInstanceName
     * @param string $integrityRepositoryPoolInstanceName
     * @param string $integrityCollectorInstanceName
     * @param string $hashGeneratorInstanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $integrityFactoryInstanceName = '\\Magento\\Csp\\Model\\SubresourceIntegrityFactory',
        $integrityRepositoryPoolInstanceName = '\\Magento\\Csp\\Model\\SubresourceIntegrityRepositoryPool',
        $integrityCollectorInstanceName = '\\Magento\\Csp\\Model\\SubresourceIntegrityCollector',
        $hashGeneratorInstanceName = '\\Magento\\Csp\\Model\\SubresourceIntegrity\\HashGenerator'
    ) {
        if (!class_exists(\Magento\Csp\Model\SubresourceIntegrity::class)) {
            return;
        }
        $this->objectManager = $objectManager;
        $this->integrityFactory = $this->create($integrityFactoryInstanceName);
        $this->integrityRepositoryPool = $this->create($integrityRepositoryPoolInstanceName);
        $this->integrityCollector = $this->create($integrityCollectorInstanceName);
        $this->hashGenerator = $this->create($hashGeneratorInstanceName);
    }

    /**
     * @param $instanceName
     * @return mixed
     */
    private function create($instanceName)
    {
        return $this->objectManager->create($instanceName);
    }

    private function getIntegrityRepository($area)
    {
        if (!isset($this->integrityRepositories[$area])) {
            $this->integrityRepositories[$area] = $this->integrityRepositoryPool->get($area);
        }
        return $this->integrityRepositories[$area];
    }

    private function isIntegrityContextEmpty($area):bool
    {
        if (!isset($this->integrityStatus[$area])) {
            $integrityRepository = $this->getIntegrityRepository($area);
            $this->integrityStatus[$area] = count($integrityRepository->getAll()) == 0;
        }
        return $this->integrityStatus[$area];
    }

    public function afterCreateBundleJsPool(
         \Swissup\Pagespeed\Model\Bundle\Manager $subject,
        $result
    ) {
        if (!class_exists(\Magento\Csp\Model\SubresourceIntegrity::class)) {
            return $result;
        }
        foreach ($result as $bundleAssetFile) {
            if (!in_array($bundleAssetFile->getContentType(), self::CONTENT_TYPES)) {
                continue;
            }
            $path = $bundleAssetFile->getPath();
            $area = explode("/", $path)[0];
            if ($this->isIntegrityContextEmpty($area)) {
                continue;
            }
            $integrityRepository = $this->getIntegrityRepository($area);
            $integrity = $integrityRepository->getByPath($path);
            if ($integrity && $integrity->getHash()) {
                continue;
            }

            $data = [
                'hash' =>  $this->hashGenerator->generate(
                    $bundleAssetFile->getContent()
                ),
                'path' => $bundleAssetFile->getPath()
            ];
            $integrity = $this->integrityFactory->create(['data' => $data]);
            $this->integrityCollector->collect($integrity);
        }

        $bunches = [];
        foreach ($this->integrityCollector->release() as $integrity) {
            $area = explode("/", $integrity->getPath())[0];

            $bunches[$area][] = $integrity;
        }

        foreach ($bunches as $area => $bunch) {
            $this->getIntegrityRepository($area)
                ->saveBunch($bunch);
        }

        return $result;
    }
}
