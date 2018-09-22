<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\Cart;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\StateException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Quote\Api\Data\EstimateAddressInterfaceFactory;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Shipping\Model\Config as ShippingConfig;

/**
 * @inheritdoc
 */
class GetAvailableShippingMethodsOnCart implements ResolverInterface
{
    /**
     * @var ShippingConfig
     */
    private $shippingConfig;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ShippingMethodManagementInterface
     */
    private $shippingMethodManagement;

    /**
     * @var EstimateAddressInterfaceFactory
     */
    private $estimatedAddressFactory;

    /**
     * GetAvailableShippingMethodsOnCart constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ShippingConfig $shippingConfig
     * @param ShippingMethodManagementInterface $shippingMethodManagement
     * @param EstimateAddressInterfaceFactory $estimatedAddressFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ShippingConfig $shippingConfig,
        ShippingMethodManagementInterface $shippingMethodManagement,
        EstimateAddressInterfaceFactory $estimatedAddressFactory
    ) {
        $this->shippingConfig = $shippingConfig;
        $this->scopeConfig = $scopeConfig;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->estimatedAddressFactory = $estimatedAddressFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }

        $cartId = $args['input']['cart_id'];

        if (isset($args['input']['customer_address_id'])) {
            $addressId = $args['input']['customer_address_id'];
            $shippingMethods = $this->shippingMethodManagement->estimateByAddressId($cartId, $addressId);
        } else if (isset($args['input']['address'])) {
            $address = $args['input']['address'];

            /** @var EstimateAddressInterface $estimatedAddress */
            $estimatedAddress = $this->estimatedAddressFactory->create();
            $estimatedAddress->setCountryId($address['country_code']);
            if (isset($address['postcode'])) {
                $estimatedAddress->setPostcode($address['postcode']);
            }
            if (isset($address['region'])) {
                $estimatedAddress->setRegion($address['region']);
            }
            if (isset($address['region_id'])) {
                $estimatedAddress->setRegionId($address['region_id']);
            }
            $shippingMethods = $this->shippingMethodManagement->estimateByAddress($cartId, $estimatedAddress);
        } else {
            try {
                $shippingMethods = $this->shippingMethodManagement->getList($cartId);
            } catch (StateException $exception) {
                // Todo load all the available shipping methods
                $shippingMethods = [];
            }
        }
        $data['available_shipping_methods'] = [];

        foreach ($shippingMethods as $shippingMethod) {
            $data['available_shipping_methods'][] = [
                'code' => $shippingMethod->getCarrierCode(),
                'label' => $shippingMethod->getCarrierTitle(),
                'error_message' => $shippingMethod->getErrorMessage() ?: '',
                'free_shipping' => $shippingMethod->getAmount() == 0,
                'is_available' => $shippingMethod->getAvailable()
            ];
        }

        return $data;
    }
}
