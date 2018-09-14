<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Model\Category\Tree;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Tree\Node;
use Magento\Catalog\Model\Category;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Catalog\Model\ResourceModel\Category\Tree as CategoryTree;

/**
 * Test for category tree data provider
 */
class CategoryTreeTest extends GraphQlAbstract
{
    /**
     * Verify the category tree does not contain inactive categories
     *
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     * @return void
     */
    public function testCategoryTree(): void
    {
        $collectedData = $this->getActiveCategoriesByRootNode();
        $query =
            <<<QUERY
{
    category(id: 2) {
        children {
            name
        }
    }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertEquals(sort($collectedData), sort($response));
    }

    /**
     * Get active categories
     *
     * @param Node $node
     * @return array
     */
    private function getActiveCategoriesByRootNode(Category $category = null): array
    {
        $categoriesList = [];
        $treeInstance = ObjectManager::getInstance()->get(Tree::class);
        $categoryTree = ObjectManager::getInstance()->get(CategoryTree::class);
        if ($category !== null) {
            $node = $categoryTree->loadNode($category->getId());
            $tree = $treeInstance->getTree($node, 1)->getChildrenData();
        } else {
            $rootNode = $treeInstance->getRootNode();
            $tree = ObjectManager::getInstance()->get(Tree::class)->getTree($rootNode, 1)->getChildrenData();
        }

        foreach ($tree as $category) {
            if ($category->getIsActive()) {
                $categoriesList[] = [
                    'name' => $category->getName(),
                ];
            }
        }

        return $categoriesList;
    }
}