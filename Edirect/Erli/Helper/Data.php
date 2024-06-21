<?php
/**
 * Created in PhpStorm.
 * by Edirect24
 * on 07, styczeń, 10:54, 2021
 * @author   biuro@edirect24.pl
 * @projekt   Erli
 * @copyright Copyright (C) 2021 Edirect24
 **/

namespace Edirect\Erli\Helper;

use Magento\Catalog\Model\Product\Attribute\Source\Status as Status;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\State;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ResourceProductTypeConfigurable;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ProductTypeConfigurable;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Framework\UrlInterface as UrlInterface;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Helper\Output as HelperOutput;

/**
 * Helper Class Data
 */
class Data extends AbstractHelper
{
    /**
     * Erli Module Name
     */
    const MODULE_NAME = 'Edirect_Erli';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfigInterface;

    /**
     * @var CollectionFactory
     */
    protected $productCollection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerInterface;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $productResourceModel;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    protected $categoryResourceModel;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    protected $eavResourceModel;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfigModel;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var StockRegistryProviderInterface
     */
    protected $stockRegistryProviderInterface;

    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfigurationInterface;

    /**
     * @var GetSalableQuantityDataBySku
     */
    protected $getSalableQuantityDataBySku;

    /**
     * @var ResourceProductTypeConfigurable
     */
    protected $resourceProductTypeConfigurable;

    /**
     * @var Rule
     */
    protected $priceRule;

    /**
     * @var Rule
     */
    protected $helperOutput;

    /**
     * Data constructor.
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfigInterface
     * @param CollectionFactory $productCollection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResourceModel
     * @param \Magento\Catalog\Model\ResourceModel\Category $categoryResourceModel
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $eavResourceModel
     * @param \Magento\Eav\Model\Config $eavConfigModel
     * @param State $state
     * @param StockRegistryProviderInterface $stockRegistryProviderInterface
     * @param StockConfigurationInterface $stockConfigurationInterface
     * @param GetSalableQuantityDataBySku $getSalableQuantityDataBySku
     * @param ResourceProductTypeConfigurable $resourceProductTypeConfigurable
     * @param Rule $priceRule
     * @param HelperOutput $helperOutput
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfigInterface,
        CollectionFactory $productCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product $productResourceModel,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResourceModel,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $eavResourceModel,
        \Magento\Eav\Model\Config $eavConfigModel,
        State $state,
        StockRegistryProviderInterface $stockRegistryProviderInterface,
        StockConfigurationInterface $stockConfigurationInterface,
        GetSalableQuantityDataBySku $getSalableQuantityDataBySku,
        ResourceProductTypeConfigurable $resourceProductTypeConfigurable,
        Rule $priceRule,
        HelperOutput $helperOutput
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfigInterface = $scopeConfigInterface;
        $this->productCollection = $productCollection;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->productRepository = $productRepository;
        $this->productResourceModel = $productResourceModel;
        $this->categoryResourceModel = $categoryResourceModel;
        $this->eavResourceModel = $eavResourceModel;
        $this->eavConfigModel = $eavConfigModel;
        $this->state = $state;
        $this->stockRegistryProviderInterface = $stockRegistryProviderInterface;
        $this->stockConfigurationInterface = $stockConfigurationInterface;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
        $this->resourceProductTypeConfigurable = $resourceProductTypeConfigurable;
        $this->priceRule = $priceRule;
        $this->helperOutput = $helperOutput;
    }

    /**
     * @return mixed
     */
    public function getApiUrl()
    {
        return $this->scopeConfigInterface->getValue('erli/general/api_url');
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->scopeConfigInterface->getValue('erli/general/api_key');
    }

    /**
     * @return bool
     */
    public function getVerifyHost()
    {
        return $this->scopeConfigInterface->getValue('erli/general/verifyhost');
    }

    /**
     * @return bool
     */
    public function getKeepAlive()
    {
        return $this->scopeConfigInterface->getValue('erli/general/keepalive');
    }

    /**
     * @return int
     */
    public function getKeepIdle()
    {
        return $this->scopeConfigInterface->getValue('erli/general/keepidle');
    }

    /**
     * @return int
     */
    public function getKeepIntvl()
    {
        return $this->scopeConfigInterface->getValue('erli/general/keepintvl');
    }

    /**
     * @return mixed
     */
    public function getShippingMethodMapping()
    {
        return $this->scopeConfigInterface->getValue('erli/shipping_method/mapping');
    }

    /**
     * @return mixed
     */
    public function getStoreForPrices()
    {
        return $this->scopeConfigInterface->getValue('erli/data/price');
    }

    /**
     * @return mixed
     */
    public function getPriceRuleEnable()
    {
        if ($this->scopeConfigInterface->getValue('erli/data/price_rule_enable') == 1) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getPriceRuleCustomerGroup()
    {
        return $this->scopeConfigInterface->getValue('erli/data/price_rule_customer_group');
    }

    /**
     * @return mixed
     */
    public function getSimpleProductDescriptionSource()
    {
        return $this->scopeConfigInterface->getValue('erli/data/simple_description');
    }

    /**
     * @param $status
     * @return mixed
     */
    public function getOrderStatus($status)
    {
        return $this->scopeConfigInterface->getValue('erli/order_status/' . $status);
    }

    /**
     * @param $status
     * @return mixed
     */
    public function getDeliveryStatus($status)
    {
        return $this->scopeConfigInterface->getValue('erli/delivery_status/' . $status);
    }

    /**
     * @return string
     */
    public function getDeliveryTrackingVendor()
    {
        return $this->scopeConfigInterface->getValue('erli/delivery_status/vendor');
    }

    public function getErliIntegrationProductCollection()
    {
        $productCollection = $this->productCollection->create();
        $productCollection
            ->addFieldToFilter('status', Status::STATUS_ENABLED)
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('type_id', [
                'neq' => ProductTypeConfigurable::TYPE_CODE
            ])
            ->addAttributeToFilter('type_id', [
                'neq' => ProductType::TYPE_BUNDLE
            ])
            ->addFieldToFilter('erli_integration', 1);

        //exclude product from collection if its child of configurable product and configurable is disabled
        foreach ($productCollection as $product) {

            $productsIds[$product->getId()] = $product->getId();

            if ($product->getTypeId() == 'simple'
                && $product->getVisibility() == ProductVisibility::VISIBILITY_NOT_VISIBLE
            ) {
                if ($this->getParentId($product->getId())) {
                    $parent = $this->productRepository->getById($this->getParentId($product->getId()));

                    if ($parent->getStatus() == Status::STATUS_DISABLED) {
                        unset($productsIds[$product->getId()]);
                    }
                }
            }

        }

        if (isset($productsIds) && count($productsIds) > 0) {
            return $productsIds;
        }

        return false;
    }

    public function getParentId($productId)
    {
        $parentByChild = $this->resourceProductTypeConfigurable->getParentIdsByChild($productId);
        if (isset($parentByChild[0])) {
            return $parentByChild[0];
        }
        return false;
    }

    public function getUpdateProductsCollection()
    {
        $productCollection = $this->productCollection->create();
        $productCollection
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('type_id', [
                'neq' => ProductTypeConfigurable::TYPE_CODE
            ])
            ->addAttributeToFilter('type_id', [
                'neq' => ProductType::TYPE_BUNDLE
            ])
            ->addFieldToFilter('erli_integration', 1)
            ->addFieldToFilter('erli_update_flag', 1)
            ->load();

        $ids = $productCollection->getAllIds();

        if (count($ids) > 0) {
            return $ids;
        }
    }

    /**
     * @param string $productSku
     */
    public function getProductInfo($productId)
    {

        $product = $this->productRepository->getById($productId, false, $this->getStoreForPrices(), false);

        $result1 = [
            'product_id' => $product->getId(),
            'name' => $product->getName(),
            'description' =>  $this->helperOutput->productAttribute(
                $product,
                $product->getDescription(),
                'description'
            ),
            'sku' => $product->getSku(),
            'type' => $product->getTypeId(),
            'categories' => $this->getCategoryNameById($product->getCategoryIds()),
            'erli_dispatch_unit' => $product->getErliDispatchUnit(),
            'erli_dispatch_period' => $product->getErliDispatchPeriod(),
        ];

        //set the lowest product price
        $productPrice = $product->getFinalPrice();

        //check that product price is generated by price rule
        if ($this->getPriceRuleEnable() && $this->getProductPriceRule($product->getId())) {
            $productPriceRule = $this->getProductPriceRule($product->getId());

            if ($productPriceRule < $productPrice) {
                $productPrice = $productPriceRule;
            }
        }

        $result1['price'] = $productPrice * 100; //penny format

        //set status for normal simple products
        if ($product->getStatus() == Status::STATUS_ENABLED) {
            $result1['status'] = 'active';
        } else {
            $result1['status'] = 'inactive';
        }

        //set description for simple product based on configuration field
        if ($product->getTypeId() == 'simple' && $this->getSimpleProductDescriptionSource() == 'configurable') {
            if ($this->getParentId($product->getId())) {
                $parentId = $this->getParentId($product->getId());
                $parent = $this->productRepository->getById($parentId);
                //$parentDescription = $parent->getDescription();
                $parentDescription = $this->helperOutput->productAttribute(
                    $parent,
                    $parent->getDescription(),
                    'description'
                );

                if (!empty($parentDescription)) {
                    $result1['description'] = $parentDescription;
                }

            }
        }

        // set status based on parent configurable product

        if ($product->getTypeId() == 'simple'
            && $product->getVisibility() == ProductVisibility::VISIBILITY_NOT_VISIBLE
        ) {

            if ($this->getParentId($product->getId())) {
                $parentId = $this->getParentId($product->getId());
                $parent = $this->productRepository->getById($parentId);

                if ($parent->getStatus() == Status::STATUS_ENABLED
                    && $product->getStatus() == Status::STATUS_ENABLED
                ) {
                    $result1['status'] = 'active';
                } else {
                    $result1['status'] = 'inactive';
                }

                $configurableAttributes = $parent->getTypeInstance(true)->getConfigurableAttributesAsArray($parent);
                $result1['erli_external_variant_group'] = $this->convertProductConfigurableAttributes(
                    $parentId,
                    $configurableAttributes
                );
            }
        }

        if (!empty($product->getEan()) && $product->getEan() != '') {
            $result1['ean'] = $product->getEan();
        }

        //set qty
        $salableQty = $this->getSalableQuantityDataBySku->execute($product->getSku());
        if ($salableQty) {
            $result1['stock'] = (int)$salableQty[0]['qty'];
        }

        // get product attributes
        $productAttributes = $product->getAttributes();
        foreach ($productAttributes as $attribute) {
            $productAttributesCodes[] = $attribute->getName();
        }

        foreach ($this->getVisibleOnFrontAttributes() as $attribute) {
            $value = $product->getResource()->getAttribute($attribute['id'])->getFrontend()->getValue($product);

            if ($attribute['type'] == 'boolean') {
                $value = $value->getText();
            }
            // check that visible attribute is assigned to product
            if (in_array($attribute['code'], $productAttributesCodes) && !empty($value)) {

                $attributes[] = [
                    'code' => $attribute['code'],
                    'name' => $attribute['name'],
                    'type' => $attribute['type'],
                    'id' => $attribute['id'],
                    'erli_type' => $this->erliAttributeTypeMap($attribute['type']),
                    'erli_values' => $this->convertValuesToErliFormat(
                        $value,
                        $this->erliAttributeTypeMap($attribute['type']),
                        $value
                    ), //vales as array
                    'values' => [$value ? $value : null]
                ];
            }

        }

        //set images
        foreach ($product->getMediaGallery('images') as $productImage) {
            $result1['images'][]['url'] = $this->storeManagerInterface
                    ->getStore()
                    ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                        . 'catalog/product'
                        . $productImage['file'];
        }

        $result1['attributes'] = [];
        if (isset($attributes)) {
            $result1['attributes'] = $attributes;
        }

        return $result1;
    }

    /**
     * @param $categoryIds
     * @return array
     */
    public function getCategoryNameById($categoryIds)
    {
        $names = [];
        foreach ($categoryIds as $categoryId) {
            $names[$categoryId] = $this->categoryResourceModel->getAttributeRawValue($categoryId, 'name', 0);
        }

        return $names;
    }

    /**
     * @param $parentId
     * @param $configurableAttributes
     * @return array|false
     */
    public function convertProductConfigurableAttributes($parentId, $configurableAttributes)
    {

        if (!empty($configurableAttributes)) {
            foreach ($configurableAttributes as $configurableAttribute) {
                $externalVariantGroupData[$parentId][] = $configurableAttribute['attribute_id'];
            }

            return $externalVariantGroupData;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getVisibleOnFrontAttributes()
    {
        $entityTypeId = $this->eavConfigModel
            ->getEntityType(ProductModel::ENTITY)
            ->getId();

        $attributes = $this->eavResourceModel
            ->setEntityTypeFilter($entityTypeId)
            ->addFieldToFilter('is_visible_on_front', 1);

        $editableAttributes = [];
        $allowedAttributesTypes = ['text', 'textarea', 'date', 'datetime', 'boolean', 'multiselect', 'select', 'price'];

        foreach ($attributes as $attribute) {
            if (in_array($attribute->getFrontendInput(), $allowedAttributesTypes)) {
                $editableAttributes[] = [
                    'name' => $attribute->getStoreLabel(),
                    'code' => $attribute->getAttributeCode(),
                    'id' => $attribute->getAttributeId(),
                    'type' => $attribute->getFrontendInput()
                ];
            }
        }

        return $editableAttributes;
    }

    /**
     * @param $attributeType
     * @return array
     */
    public function erliAttributeTypeMap($attributeType)
    {
        if (in_array($attributeType, ['multiselect', 'select'])) {
            return 'dictionary';
        }

        return 'string';
    }

    /**
     * @param $attributeType
     * @param $attributeValue
     */
    public function convertValuesToErliFormat($attributeCode, $attributeType, $attributeValue)
    {

        switch ($attributeType) {
            case 'dictionary':
                $attributeValueParsed = explode(',', $attributeValue);
                if (count($attributeValueParsed) > 0) {

                    foreach ($attributeValueParsed as $value) {
                        $attributeValueParsedArray[] = [
                            'id' => $attributeCode,
                            'name' => $value
                        ];
                    }

                    return $attributeValueParsedArray;
                }
                break;

            default:
                return [$attributeValue];
        }
    }

    /**
     * @param $productId
     * @return mixed
     */
    public function getProductErliPriceList($productId)
    {
        $product = $this->productRepository->getById(
            $productId,
            false,
            $this->getStoreForPrices(),
            false
        );
        $productPriceList = $product->getErliPriceList();

        return $productPriceList;
    }

    /**
     * @param $productId
     * @return false|float
     */
    public function getProductPriceRule($productId)
    {

        $priceFromRule = $this->priceRule->getRulePrice(
            new \DateTime(),
            1,
            $this->getPriceRuleCustomerGroup(),
            $productId
        );

        if (!empty($priceFromRule)) {
            return $priceFromRule;
        }

        return false;
    }
}
