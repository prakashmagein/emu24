<?php
declare(strict_types=1);

namespace Swissup\Navigationpro\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Swissup\Navigationpro\Model\Resolver\DataProvider\Menu as DataProvider;
use Magento\Catalog\Api\CategoryRepositoryInterface;

class Menu implements ResolverInterface
{
    /**
     * @var \Swissup\Navigationpro\Model\Resolver\DataProvider\Menu
     */
    private $dataProvider;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Swissup\Navigationpro\GraphQl\Query\Uid
     */
    private $uidEncoder;

    /**
     * @param DataProvider $dataProvider
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Swissup\Navigationpro\GraphQl\Query\Uid $uidEncoder
     */
    public function __construct(
        DataProvider $dataProvider,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Swissup\Navigationpro\GraphQl\Query\Uid $uidEncoder
    ) {
        $this->dataProvider = $dataProvider;
        $this->categoryRepository = $categoryRepository;
        $this->scopeConfig = $scopeConfig;
        $this->uidEncoder = $uidEncoder;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        /** @var $store \Magento\Store\Api\Data\StoreInterface */
        $store = $context->getExtensionAttributes()->getStore();
        $storeId = (int) $store->getId();

        if (!isset($args['identifier'])) {
            $args['identifier'] = $this->scopeConfig->getValue(
                \Swissup\Navigationpro\Helper\Data::CONFIG_PATH_TOPMENU,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );

            if (empty($args['identifier'])) {
                throw new GraphQlInputException(__('menu identifier is required .'));
            }
        }

        if (!isset($args['categoryId'])) {
            $args['categoryId'] = $store->getRootCategoryId();
        } elseif ($this->uidEncoder->isValidBase64($args['categoryId'])) {
            $args['categoryId'] = $this->uidEncoder->decode($args['categoryId']);
        }

        try {
            $provider = $this->dataProvider;
            $provider->setIdentifier((string) $args['identifier']);
            $provider->setStoreId($storeId);

            $categoryId = (int) $args['categoryId'];
            $provider->setCategoryId($categoryId);

            $category = $this->categoryRepository->get($categoryId, $storeId);
            if ($category->getId()) {
                $provider->setCurrentCategory($category);
            }
            $provider->setOutermostClass('level-top');

            $widgetParameters = new \Magento\Framework\DataObject();
            $provider->setWidgetParameters($widgetParameters);

            $data = $provider->getData();
//        } catch (NoSuchEntityException $e) {
        } catch (\Exception $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }

        return $data;
    }
}
