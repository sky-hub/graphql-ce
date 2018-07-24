<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Resolver\Quote\QuoteDataProvider;

/**
 * StoreConfig page field resolver, used for GraphQL request processing.
 */
class QuoteResolver implements ResolverInterface
{
    /**
     * @var QuoteDataProvider
     */
    private $quoteDataProvider;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param QuoteDataProvider $quoteDataProvider
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        QuoteDataProvider $quoteDataProvider,
        ValueFactory $valueFactory
    ) {
        $this->valueFactory = $valueFactory;
        $this->quoteDataProvider = $quoteDataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) : Value {

        $quoteData = $this->quoteDataProvider->getData();

        $result = function () use ($storeConfigData) {
            return !empty($storeConfigData) ? $storeConfigData : [];
        };

        return $this->valueFactory->create($result);
    }
}
