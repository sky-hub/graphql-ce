<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Review;

use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for retrieving customer product reviews
 */
class CustomerProductReviewsTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Review/_files/customer_reviews.php
     */
    public function testGetCustomerProductReviews()
    {
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';

        $query = <<<QUERY
query {
    productReviews {
        items {
            review_id
            entity_id
            store_id
            entity_name
            title
            detail
            sum
            count
            nickname
            created_at
        }
    }
}
QUERY;

        $response = $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));

        $this->assertNotEmpty($response['productReviews']['items']);
        $this->assertInternalType('array', $response['productReviews']['items']);
        $this->assertCount(4, $response['productReviews']['items']);

        foreach ($this->getExpectedData() as $key => $data) {
            $this->assertEquals($data['entity_id'], $response['productReviews']['items'][$key]['entity_id']);
            $this->assertEquals($data['store_id'], $response['productReviews']['items'][$key]['store_id']);
            $this->assertEquals($data['entity_name'], $response['productReviews']['items'][$key]['entity_name']);
            $this->assertEquals($data['title'], $response['productReviews']['items'][$key]['title']);
            $this->assertEquals($data['detail'], $response['productReviews']['items'][$key]['detail']);
            $this->assertEquals($data['nickname'], $response['productReviews']['items'][$key]['nickname']);
        }
    }

    /**
     * @param string $email
     * @param string $password
     * @return array
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    private function getCustomerAuthHeaders(string $email, string $password): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, $password);
        return ['Authorization' => 'Bearer ' . $customerToken];
    }

    /**
     * Get expected data
     *
     * @return array
     */
    private function getExpectedData()
    {
        return [
            [
                'entity_id' => 1,
                'store_id' => 1,
                'entity_name' => 'Simple Product',
                'title' => 'Not Approved Review Summary',
                'detail' => 'Review text',
                'nickname' => 'Nickname',
            ],
            [
                'entity_id' => 1,
                'store_id' => 1,
                'entity_name' => 'Simple Product',
                'title' => 'Approved Review Summary',
                'detail' => 'Review text',
                'nickname' => 'Nickname',
            ],
            [
                'entity_id' => 1,
                'store_id' => 1,
                'entity_name' => 'Simple Product',
                'title' => 'Secondary Approved Review Summary',
                'detail' => 'Review text',
                'nickname' => 'Nickname',
            ],
            [
                'entity_id' => 1,
                'store_id' => 1,
                'entity_name' => 'Simple Product',
                'title' => 'Pending Review Summary',
                'detail' => 'Review text',
                'nickname' => 'Nickname',
            ],
        ];
    }
}
