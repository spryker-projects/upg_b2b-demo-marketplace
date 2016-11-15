<?php

/**
 * This file is part of the Spryker Demoshop.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\Importer\Business\Importer\Category;

use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\NodeTransfer;
use LogicException;
use Orm\Zed\Category\Persistence\Base\SpyCategoryNodeQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Pyz\Zed\Category\Business\CategoryFacadeInterface;
use Spryker\Shared\Library\Collection\Collection;
use Spryker\Zed\Category\Persistence\CategoryQueryContainerInterface;
use Spryker\Zed\Locale\Business\LocaleFacadeInterface;

class CategoryHierarchyImporter extends AbstractCategoryImporter
{

    const PARENT_KEY = 'parentKey';

    /**
     * @var \Spryker\Zed\Category\Persistence\CategoryQueryContainerInterface
     */
    protected $categoryQueryContainer;

    /**
     * @var \Spryker\Shared\Library\Collection\CollectionInterface
     */
    protected $nodeToKeyMapperCollection;

    /**
     * @var \Orm\Zed\Category\Persistence\SpyCategoryNode
     */
    protected $defaultRootNode;

    /**
     * @param \Spryker\Zed\Locale\Business\LocaleFacadeInterface $localeFacade
     * @param \Pyz\Zed\Category\Business\CategoryFacadeInterface $categoryFacade
     * @param \Spryker\Zed\Category\Persistence\CategoryQueryContainerInterface $categoryQueryContainer
     */
    public function __construct(
        LocaleFacadeInterface $localeFacade,
        CategoryFacadeInterface $categoryFacade,
        CategoryQueryContainerInterface $categoryQueryContainer
    ) {
        parent::__construct($localeFacade, $categoryFacade);

        $this->categoryQueryContainer = $categoryQueryContainer;
        $this->nodeToKeyMapperCollection = new Collection([]);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Category Tree';
    }

    /**
     * @return bool
     */
    public function isImported()
    {
        $query = SpyCategoryNodeQuery::create();
        $query->filterByIsRoot(false)
            ->filterByFkParentCategoryNode(null, Criteria::ISNULL);

        return $query->count() === 0;
    }

    /**
     * @DRY
     *
     * @see \Pyz\Zed\Importer\Business\Importer\Product\ProductCategoryImporter::getRootNode()
     *
     * @throws \LogicException
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryNode
     */
    protected function getRootNode()
    {
        if ($this->defaultRootNode === null) {
            $queryRoot = $this->categoryQueryContainer->queryRootNode();
            $this->defaultRootNode = $queryRoot->findOne();

            if ($this->defaultRootNode === null) {
                throw new LogicException('Could not find any root nodes');
            }
        }

        return $this->defaultRootNode;
    }

    /**
     * @param array $data
     *
     * @return void
     */
    protected function importOne(array $data)
    {
        $categoryTransfer = $this->format($data);
        $categoryTransfer = $this->updateCategoryTransferFromExistingEntity($categoryTransfer, $data[static::UCATID]);

        $idParentNode = $this->getParentNodeId($data[static::PARENT_KEY]);
        $nodes = $this->findMainCategoryNodesByCategoryKey($data[static::UCATID]);

        foreach ($nodes as $nodeEntity) {
            $nodeTransfer = new NodeTransfer();
            $nodeTransfer->fromArray($nodeEntity->toArray(), true);
            $categoryTransfer->setCategoryNode($nodeTransfer);

            $parentNodeTransfer = new NodeTransfer();
            $parentNodeTransfer->setIdCategoryNode($idParentNode);
            $categoryTransfer->setParentCategoryNode($parentNodeTransfer);

            $this->categoryFacade->update($categoryTransfer);
        }
    }

    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     * @param string $categoryKey
     *
     * @return \Generated\Shared\Transfer\CategoryTransfer
     */
    protected function updateCategoryTransferFromExistingEntity(CategoryTransfer $categoryTransfer, $categoryKey)
    {
        $categoryEntity = $this
            ->categoryQueryContainer
            ->queryCategoryByKey($categoryKey)
            ->findOne();

        $categoryTransfer->fromArray($categoryEntity->toArray(), true);

        return $categoryTransfer;
    }

    /**
     * @param string $parentKey
     *
     * @return int
     */
    protected function getParentNodeId($parentKey)
    {
        $idParentNode = $this->getRootNode()->getIdCategoryNode();

        if (!$this->nodeToKeyMapperCollection->has($parentKey)) {
            $parent = $this->categoryQueryContainer
                ->queryMainCategoryNodeByCategoryKey($parentKey)
                ->findOne();

            if ($parent) {
                $idParentNode = $parent->getIdCategoryNode();
                $this->nodeToKeyMapperCollection->set($parentKey, $idParentNode);
            }
        } else {
            $idParentNode = $this->nodeToKeyMapperCollection->get($parentKey);
        }

        return $idParentNode;
    }

    /**
     * @param string $categoryKey
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryNode[]|\Propel\Runtime\Collection\ObjectCollection
     */
    protected function findMainCategoryNodesByCategoryKey($categoryKey)
    {
        return $this
            ->categoryQueryContainer
            ->queryNodeByCategoryKey($categoryKey)
            ->filterByIsMain(true)
            ->find();
    }

}
