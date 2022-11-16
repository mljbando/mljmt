<?php

namespace Customize\Twig\Extension;

use Doctrine\Common\Collections;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\Category;
use Eccube\Entity\Master\ProductStatus;
use Eccube\Entity\Product;
use Eccube\Entity\ProductClass;
use Eccube\Repository\ProductRepository;
use Twig_SimpleFunction;

class TwigExtension extends \Twig_Extension
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * TwigExtension constructor.
     *
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        EccubeConfig $eccubeConfig,
        ProductRepository $productRepository
    ) {
        $this->entityManager = $entityManager;
        $this->eccubeConfig = $eccubeConfig;
        $this->productRepository = $productRepository;
    }
    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('CustomizeNewProduct', array($this, 'getCustomizeNewProduct')),
            new Twig_SimpleFunction('ExistsCategoryList', [$this, 'getExistsCategoryList']),
        );
    }

    /**
     * Name of this extension
     *
     * @return string
     */
    public function getName()
    {
        return 'CustomizeTwigExtension';
    }

    /**
     * @param Category|null $Parent
     * @param $flat
     * @return array
     */
    public function getExistsCategoryList(Category $Parent = null, $flat = false)
    {
        $qb = $this->entityManager->getRepository('Eccube\Entity\Category')
            ->createQueryBuilder('c1')
            ->select('c1, c2, c3, c4, c5')
            ->leftJoin('c1.Children', 'c2')
            ->leftJoin('c2.Children', 'c3')
            ->leftJoin('c3.Children', 'c4')
            ->leftJoin('c4.Children', 'c5')
            ->orderBy('c1.sort_no', 'DESC')
            ->addOrderBy('c2.sort_no', 'DESC')
            ->addOrderBy('c3.sort_no', 'DESC')
            ->addOrderBy('c4.sort_no', 'DESC')
            ->addOrderBy('c5.sort_no', 'DESC')
        ;

        if ($Parent) {
            $qb->where('c1.Parent = :Parent')->setParameter('Parent', $Parent);
        } else {
            $qb->where('c1.Parent IS NULL');
        }
        $qb->andWhere('(
              c5.id IN (SELECT DISTINCT pcx1.category_id FROM Eccube\Entity\ProductCategory pcx1) OR
              c1.id IN (SELECT DISTINCT pcx2.category_id FROM Eccube\Entity\ProductCategory pcx2) OR
              c2.id IN (SELECT DISTINCT pcx3.category_id FROM Eccube\Entity\ProductCategory pcx3) OR
              c3.id IN (SELECT DISTINCT pcx4.category_id FROM Eccube\Entity\ProductCategory pcx4) OR
              c4.id IN (SELECT DISTINCT pcx5.category_id FROM Eccube\Entity\ProductCategory pcx5)
            )');

        $Categories = $qb->getQuery()
//            ->getSQL();
//            ->useResultCache(true, $this->getCacheLifetime())
            ->getResult();

        if ($flat) {
            $array = [];
            foreach ($Categories as $Category) {
//                $array = array_merge($array, $Category->getSelfAndDescendants());
//                resolve greedy function
                foreach ($Category->getSelfAndDescendants() as $key => $value) {
                    $array[$key] = $value;
                }
            }
            $Categories = $array;
        }

        return $Categories;
    }

    /**
     *
     * 新着商品を4件返す
     *
     * @return Products|null
     */
    public function getCustomizeNewProduct()
    {
        try {
            //検索条件の新着順を定義
            $searchData = array();
            $qb = $this->entityManager->createQueryBuilder();
            $query = $qb->select("plob")
                ->from("Eccube\\Entity\\Master\\ProductListOrderBy", "plob")
                ->where('plob.id = :id')
                ->setParameter('id', $this->eccubeConfig['eccube_product_order_newer'])
                ->getQuery();
            $searchData['orderby'] = $query->getOneOrNullResult();

            //商品情報を4件取得
            $qb = $this->productRepository->getQueryBuilderBySearchData($searchData);
            $query = $qb->setMaxResults(4)->getQuery();
            $products = $query->getResult();
            return $products;

        } catch (\Exception $e) {
            return null;
        }
        return null;
    }
}
