<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\Quote;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\GuestCartRepositoryInterface;

/**
 * QuoteGraphQl field data provider, used for GraphQL request processing.
 */
class QuoteDataProvider
{
    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var GuestCartRepositoryInterface
     */
    protected $guestCartRepository;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param GuestCartRepositoryInterface $guestCartRepository
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        GuestCartRepositoryInterface $guestCartRepository
    ) {
        $this->cartRepository = $cartRepository;
        $this->guestCartRepository = $guestCartRepository;
    }

    /**
     * @return []
     */
    public function getData(string $quoteId): array
    {
        try {
            if (is_numeric($quoteId)) {
                $quote = $this->cartRepository->get($quoteId);
            } else {
                $quote = $this->guestCartRepository->get($quoteId);
            }
        } catch (NoSuchEntityException $ex) {
            return [];
        }



        return [];
    }
}
