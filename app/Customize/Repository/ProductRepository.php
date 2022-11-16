<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Customize\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Common\EccubeConfig;
use Eccube\Doctrine\Query\Queries;
use Eccube\Entity\Product;
use Eccube\Entity\ProductStock;
use Eccube\Repository\QueryKey;
use Eccube\Util\StringUtil;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Eccube\Repository\AbstractRepository;

/**
 * ProductRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProductRepository extends AbstractRepository
{
    /**
     * @var Queries
     */
    protected $queries;

    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    public const COLUMNS = [
        'product_id' => 'p.id'
        ,'name' => 'p.name'
        ,'product_code' => 'pc.code'
        ,'stock' => 'pc.stock'
        ,'status' => 'p.Status'
        ,'create_date' => 'p.create_date'
        ,'update_date' => 'p.update_date'
    ];

    /**
     * ProductRepository constructor.
     *
     * @param RegistryInterface $registry
     * @param Queries $queries
     * @param EccubeConfig $eccubeConfig
     */
    public function __construct(
        RegistryInterface $registry,
        Queries $queries,
        EccubeConfig $eccubeConfig
    ) {
        parent::__construct($registry, Product::class);
        $this->queries = $queries;
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * Find the Product with sorted ClassCategories.
     *
     * @param integer $productId
     *
     * @return Product
     */
    public function findWithSortedClassCategories($productId)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->addSelect(['pc', 'cc1', 'cc2', 'pi', 'pt'])
            ->innerJoin('p.ProductClasses', 'pc')
            ->leftJoin('pc.ClassCategory1', 'cc1')
            ->leftJoin('pc.ClassCategory2', 'cc2')
            ->leftJoin('p.ProductImage', 'pi')
            ->leftJoin('p.ProductTag', 'pt')
            ->where('p.id = :id')
            ->andWhere('pc.visible = :visible')
            ->setParameter('id', $productId)
            ->setParameter('visible', true)
            ->orderBy('cc1.sort_no', 'DESC')
            ->addOrderBy('cc2.sort_no', 'DESC');

        $product = $qb
            ->getQuery()
            ->getSingleResult();

        return $product;
    }

    /**
     * Find the Products with sorted ClassCategories.
     *
     * @param array $ids Product in ids
     * @param string $indexBy The index for the from.
     *
     * @return ArrayCollection|array
     */
    public function findProductsWithSortedClassCategories(array $ids, $indexBy = null)
    {
        if (count($ids) < 1) {
            return [];
        }
        $qb = $this->createQueryBuilder('p', $indexBy);
        $qb->addSelect(['pc', 'cc1', 'cc2', 'pi', 'pt', 'tr', 'ps'])
            ->innerJoin('p.ProductClasses', 'pc')
            // XXX Joined 'TaxRule' and 'ProductStock' to prevent lazy loading
            ->leftJoin('pc.TaxRule', 'tr')
            ->innerJoin('pc.ProductStock', 'ps')
            ->leftJoin('pc.ClassCategory1', 'cc1')
            ->leftJoin('pc.ClassCategory2', 'cc2')
            ->leftJoin('p.ProductImage', 'pi')
            ->leftJoin('p.ProductTag', 'pt')
            ->where($qb->expr()->in('p.id', $ids))
            ->andWhere('pc.visible = :visible')
            ->setParameter('visible', true)
            ->orderBy('cc1.sort_no', 'DESC')
            ->addOrderBy('cc2.sort_no', 'DESC');

        $products = $qb
            ->getQuery()
            ->useResultCache(true, $this->eccubeConfig['eccube_result_cache_lifetime_short'])
            ->getResult();

        return $products;
    }

    /**
     * get query builder.
     *
     * @param  array $searchData
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderBySearchData($searchData)
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.Status = 1')
            ->andWhere('p.visible = 1')
            ->andWhere('p.out_of_print = 0')
        ;

        // 有効期間
        $qb
            // 申請期間（表示側で制御)
//            ->andWhere(
//                $qb->expr()
//                    ->orX('p.applicable_on_from < :ApplicableOnFrom', 'p.applicable_on_from is null')
//            )
//            ->setParameter('ApplicableOnFrom', new \DateTime())
//            ->andWhere(
//                $qb->expr()
//                    ->orX('p.applicable_on_to >= :ApplicableOnTo', 'p.applicable_on_to is null')
//            )
//            ->setParameter('ApplicableOnTo', new \DateTime())
            ->andWhere(
                $qb->expr()
                ->orX('p.published_at_from < :PublishAtFrom', 'p.published_at_from is null')
            )
            ->setParameter('PublishAtFrom', new \DateTime())
            ->andWhere(
                $qb->expr()
                ->orX('p.published_at_to >= :PublishAtTo', 'p.published_at_to is null')
            )
            ->setParameter('PublishAtTo', new \DateTime())
//            ->andWhere(
//                $qb->expr()
//                ->orX('p.delivered_on_from < :DeliveredOnFrom', 'p.delivered_on_from is null')
//            )
//            ->setParameter('DeliveredOnFrom', new \DateTime())
//            ->andWhere(
//                $qb->expr()
//                ->orX('p.delivered_on_to >= :DeliveredOnTo', 'p.delivered_on_to is null')
//            )
//            ->setParameter('DeliveredOnTo', new \DateTime())
        ;

        // category
        $categoryJoin = false;
        if (!empty($searchData['category_id']) && $searchData['category_id']) {
            $Categories = $searchData['category_id']->getSelfAndDescendants();
            if ($Categories) {
                $qb
                    ->innerJoin('p.ProductCategories', 'pct')
                    ->innerJoin('pct.Category', 'c')
                    ->andWhere($qb->expr()->in('pct.Category', ':Categories'))
                    ->setParameter('Categories', $Categories);
                $categoryJoin = true;
            }
        }

        if (
            (!empty($searchData['min_price']) && $searchData['min_price'])
            || (!empty($searchData['max_price']) && $searchData['max_price'])
        ) {
            $minPrice = $searchData['min_price'];
            $maxPrice = $searchData['max_price'];
            if ($minPrice) {
                $qb
                    ->andWhere(
                        "EXISTS (SELECT pcmin FROM \Eccube\Entity\ProductClass pcmin WHERE p = pcmin.Product AND pcmin.price02 >= :minPrice AND pcmin.visible = true)"
                    )
                    ->setParameter('minPrice', $minPrice);
                $classJoin = true;
            }
            if ($maxPrice) {
                $qb
                    ->andWhere(
                        "EXISTS (SELECT pcmax FROM \Eccube\Entity\ProductClass pcmax WHERE p = pcmax.Product AND pcmax.price02 <= :maxPrice AND pcmax.visible = true)"
                    )
                    ->setParameter('maxPrice', $maxPrice);
                $classJoin = true;
            }
        }
        // name
        if (isset($searchData['name']) && StringUtil::isNotBlank($searchData['name'])) {
            $keywords = preg_split('/[\s　]+/u', str_replace(['%', '_'], ['\\%', '\\_'], $searchData['name']), -1, PREG_SPLIT_NO_EMPTY);

            foreach ($keywords as $index => $keyword) {
                $key = sprintf('keyword%s', $index);
                $qb
                    ->andWhere(sprintf('NORMALIZE(p.name) LIKE NORMALIZE(:%s) OR
                        NORMALIZE(p.search_word) LIKE NORMALIZE(:%s) OR
                        NORMALIZE(p.operator_name) LIKE NORMALIZE(:%s) OR
                        EXISTS (SELECT wpc%d FROM \Eccube\Entity\ProductClass wpc%d
                        WHERE p = wpc%d.Product AND NORMALIZE(wpc%d.code) LIKE NORMALIZE(:%s))',
                        $key, $key, $key, $index, $index, $index, $index, $key
                    ))
                    ->setParameter($key, '%'.$keyword.'%');

            }
        }

        // 在庫があるもののみ
//        if (isset($searchData['has_stock']) && $searchData['has_stock']) {
//
//            $qb
//                ->innerJoin('p.ProductClasses', 'pc')
//                ->andWhere(
//                    $qb->expr()
//                        ->orX('pc.stock > 0', 'pc.stock_unlimited = 1')
//                );
//        }

        // Order By
        // 価格低い順
        $config = $this->eccubeConfig;
        if (!empty($searchData['orderby']) && $searchData['orderby']->getId() == $config['eccube_product_order_price_lower']) {
            //@see http://doctrine-orm.readthedocs.org/en/latest/reference/dql-doctrine-query-language.html
            $qb->addSelect('MIN(pc.price02) as HIDDEN price02_min');
            $qb->innerJoin('p.ProductClasses', 'pc');
            $qb->andWhere('pc.visible = true');
            $qb->groupBy('p.id');
            $qb->orderBy('price02_min', 'ASC');
            $qb->addOrderBy('p.id', 'DESC');
        // 価格高い順
        } elseif (!empty($searchData['orderby']) && $searchData['orderby']->getId() == $config['eccube_product_order_price_higher']) {
            $qb->addSelect('MAX(pc.price02) as HIDDEN price02_max');
            $qb->innerJoin('p.ProductClasses', 'pc');
            $qb->andWhere('pc.visible = true');
            $qb->groupBy('p.id');
            $qb->orderBy('price02_max', 'DESC');
            $qb->addOrderBy('p.id', 'DESC');
        // 新着順
        } elseif (!empty($searchData['orderby']) && $searchData['orderby']->getId() == $config['eccube_product_order_newer']) {
            // 在庫切れ商品非表示の設定が有効時対応
            // @see https://github.com/EC-CUBE/ec-cube/issues/1998
            if ($this->getEntityManager()->getFilters()->isEnabled('option_nostock_hidden') == true) {
                $qb->innerJoin('p.ProductClasses', 'pc');
                $qb->andWhere('pc.visible = true');
            }
            $qb->orderBy('p.create_date', 'DESC');
            $qb->addOrderBy('p.id', 'DESC');
        } else {
            if ($categoryJoin === false) {
                $qb
                    ->leftJoin('p.ProductCategories', 'pct')
                    ->leftJoin('pct.Category', 'c');
            }
            $qb
                ->addOrderBy('p.id', 'DESC');
        }

        return $this->queries->customize(QueryKey::PRODUCT_SEARCH, $qb, $searchData);
    }

    /**
     * get query builder.
     *
     * @param  array $searchData
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderBySearchDataForAdmin($searchData)
    {
        $qb = $this->createQueryBuilder('p')
            ->addSelect('pc', 'pi', 'tr', 'ps')
            ->innerJoin('p.ProductClasses', 'pc')
            ->leftJoin('p.ProductImage', 'pi')
            ->leftJoin('pc.TaxRule', 'tr')
            ->leftJoin('pc.ProductStock', 'ps')
            ->andWhere('pc.visible = :visible')
            ->setParameter('visible', true);

        // id
        if (isset($searchData['id']) && StringUtil::isNotBlank($searchData['id'])) {
            $id = preg_match('/^\d{0,10}$/', $searchData['id']) ? $searchData['id'] : null;
            if ($id && $id > '2147483647' && $this->isPostgreSQL()) {
                $id = null;
            }
            $qb
                ->andWhere('p.id = :id OR p.name LIKE :likeid OR pc.code LIKE :likeid')
                ->setParameter('id', $id)
                ->setParameter('likeid', '%'.str_replace(['%', '_'], ['\\%', '\\_'], $searchData['id']).'%');
        }

        // code
        /*
        if (!empty($searchData['code']) && $searchData['code']) {
            $qb
                ->innerJoin('p.ProductClasses', 'pc')
                ->andWhere('pc.code LIKE :code')
                ->setParameter('code', '%' . $searchData['code'] . '%');
        }

        // name
        if (!empty($searchData['name']) && $searchData['name']) {
            $keywords = preg_split('/[\s　]+/u', $searchData['name'], -1, PREG_SPLIT_NO_EMPTY);
            foreach ($keywords as $keyword) {
                $qb
                    ->andWhere('p.name LIKE :name')
                    ->setParameter('name', '%' . $keyword . '%');
            }
        }
       */

        // category
        if (!empty($searchData['category_id']) && $searchData['category_id']) {
            $Categories = $searchData['category_id']->getSelfAndDescendants();
            if ($Categories) {
                $qb
                    ->innerJoin('p.ProductCategories', 'pct')
                    ->innerJoin('pct.Category', 'c')
                    ->andWhere($qb->expr()->in('pct.Category', ':Categories'))
                    ->setParameter('Categories', $Categories);
            }
        }

        // status
        if (!empty($searchData['status']) && $searchData['status']) {
            $qb
                ->andWhere($qb->expr()->in('p.Status', ':Status'))
                ->setParameter('Status', $searchData['status']);
        }

        // link_status
        if (isset($searchData['link_status']) && !empty($searchData['link_status'])) {
            $qb
                ->andWhere($qb->expr()->in('p.Status', ':Status'))
                ->setParameter('Status', $searchData['link_status']);
        }

        // stock status
        if (isset($searchData['stock_status'])) {
            $qb
                ->andWhere('pc.stock_unlimited = :StockUnlimited AND pc.stock = 0')
                ->setParameter('StockUnlimited', $searchData['stock_status']);
        }

        // stock status
        if (isset($searchData['stock']) && !empty($searchData['stock'])) {
            switch ($searchData['stock']) {
                case [ProductStock::IN_STOCK]:
                    $qb->andWhere('pc.stock_unlimited = true OR pc.stock > 0');
                    break;
                case [ProductStock::OUT_OF_STOCK]:
                    $qb->andWhere('pc.stock_unlimited = false AND pc.stock <= 0');
                    break;
                default:
                    // 共に選択された場合は全権該当するので検索条件に含めない
            }
        }

        // tag
        if (!empty($searchData['tag_id']) && $searchData['tag_id']) {
            $qb
                ->innerJoin('p.ProductTag', 'pt')
                ->andWhere('pt.Tag = :tag_id')
                ->setParameter('tag_id', $searchData['tag_id']);
        }

        // crate_date
        if (!empty($searchData['create_datetime_start']) && $searchData['create_datetime_start']) {
            $date = $searchData['create_datetime_start'];
            $qb
                ->andWhere('p.create_date >= :create_date_start')
                ->setParameter('create_date_start', $date);
        } elseif (!empty($searchData['create_date_start']) && $searchData['create_date_start']) {
            $date = $searchData['create_date_start'];
            $qb
                ->andWhere('p.create_date >= :create_date_start')
                ->setParameter('create_date_start', $date);
        }

        if (!empty($searchData['create_datetime_end']) && $searchData['create_datetime_end']) {
            $date = $searchData['create_datetime_end'];
            $qb
                ->andWhere('p.create_date < :create_date_end')
                ->setParameter('create_date_end', $date);
        } elseif (!empty($searchData['create_date_end']) && $searchData['create_date_end']) {
            $date = clone $searchData['create_date_end'];
            $date = $date
                ->modify('+1 days');
            $qb
                ->andWhere('p.create_date < :create_date_end')
                ->setParameter('create_date_end', $date);
        }

        // update_date
        if (!empty($searchData['update_datetime_start']) && $searchData['update_datetime_start']) {
            $date = $searchData['update_datetime_start'];
            $qb
                ->andWhere('p.update_date >= :update_date_start')
                ->setParameter('update_date_start', $date);
        } elseif (!empty($searchData['update_date_start']) && $searchData['update_date_start']) {
            $date = $searchData['update_date_start'];
            $qb
                ->andWhere('p.update_date >= :update_date_start')
                ->setParameter('update_date_start', $date);
        }

        if (!empty($searchData['update_datetime_end']) && $searchData['update_datetime_end']) {
            $date = $searchData['update_datetime_end'];
            $qb
                ->andWhere('p.update_date < :update_date_end')
                ->setParameter('update_date_end', $date);
        } elseif (!empty($searchData['update_date_end']) && $searchData['update_date_end']) {
            $date = clone $searchData['update_date_end'];
            $date = $date
                ->modify('+1 days');
            $qb
                ->andWhere('p.update_date < :update_date_end')
                ->setParameter('update_date_end', $date);
        }

        // Order By
        if (isset($searchData['sortkey']) && !empty($searchData['sortkey'])) {
            $sortOrder = (isset($searchData['sorttype']) && $searchData['sorttype'] === 'a') ? 'ASC' : 'DESC';

            $qb->orderBy(self::COLUMNS[$searchData['sortkey']], $sortOrder);
            $qb->addOrderBy('p.update_date', 'DESC');
            $qb->addOrderBy('p.id', 'DESC');
        } else {
            $qb->orderBy('p.update_date', 'DESC');
            $qb->addOrderBy('p.id', 'DESC');
        }

        return $this->queries->customize(QueryKey::PRODUCT_SEARCH_ADMIN, $qb, $searchData);
    }

    public function getQueryBuilderBySearchDataForCount($searchData)
    {
        $qb = $this->createQueryBuilder('p')
            ->select('count(p.id) as cnt')
            ->andWhere('p.Status = 1')
            ->andWhere('p.visible = 1')
            ->andWhere('p.out_of_print = 0')
        ;

        // 有効期間
        $qb
            // 申請期間（表示側で制御)
//            ->andWhere(
//                $qb->expr()
//                    ->orX('p.applicable_on_from < :ApplicableOnFrom', 'p.applicable_on_from is null')
//            )
//            ->setParameter('ApplicableOnFrom', new \DateTime())
//            ->andWhere(
//                $qb->expr()
//                    ->orX('p.applicable_on_to >= :ApplicableOnTo', 'p.applicable_on_to is null')
//            )
//            ->setParameter('ApplicableOnTo', new \DateTime())
            ->andWhere(
                $qb->expr()
                    ->orX('p.published_at_from < :PublishAtFrom', 'p.published_at_from is null')
            )
            ->setParameter('PublishAtFrom', new \DateTime())
            ->andWhere(
                $qb->expr()
                    ->orX('p.published_at_to >= :PublishAtTo', 'p.published_at_to is null')
            )
            ->setParameter('PublishAtTo', new \DateTime())
//            ->andWhere(
//                $qb->expr()
//                    ->orX('p.delivered_on_from < :DeliveredOnFrom', 'p.delivered_on_from is null')
//            )
//            ->setParameter('DeliveredOnFrom', new \DateTime())
//            ->andWhere(
//                $qb->expr()
//                    ->orX('p.delivered_on_to >= :DeliveredOnTo', 'p.delivered_on_to is null')
//            )
//            ->setParameter('DeliveredOnTo', new \DateTime())
        ;

        // category
        $categoryJoin = false;
        if (!empty($searchData['category_id']) && $searchData['category_id']) {
            $Categories = $searchData['category_id']->getSelfAndDescendants();
            if ($Categories) {
                $qb
                    ->innerJoin('p.ProductCategories', 'pct')
                    ->innerJoin('pct.Category', 'c')
                    ->andWhere($qb->expr()->in('pct.Category', ':Categories'))
                    ->setParameter('Categories', $Categories);
                $categoryJoin = true;
            }
        }
        // price range
        $classJoin = false;
        if (
            (!empty($searchData['min_price']) && $searchData['min_price'])
            || (!empty($searchData['max_price']) && $searchData['max_price'])
        ) {
            $minPrice = $searchData['min_price'];
            $maxPrice = $searchData['max_price'];
            if ($minPrice) {
                $qb
                    ->andWhere(
                        "EXISTS (SELECT pcmin FROM \Eccube\Entity\ProductClass pcmin WHERE p = pcmin.Product AND pcmin.price02 >= :minPrice AND pcmin.visible = true)"
                    )
                    ->setParameter('minPrice', $minPrice);
                $classJoin = true;
            }
            if ($maxPrice) {
                $qb
                    ->andWhere(
                        "EXISTS (SELECT pcmax FROM \Eccube\Entity\ProductClass pcmax WHERE p = pcmax.Product AND pcmax.price02 <= :maxPrice AND pcmax.visible = true)"
                    )
                    ->setParameter('maxPrice', $maxPrice);
                $classJoin = true;
            }
        }

        // name
        if (isset($searchData['name']) && StringUtil::isNotBlank($searchData['name'])) {
            $keywords = preg_split('/[\s　]+/u', str_replace(['%', '_'], ['\\%', '\\_'], $searchData['name']), -1, PREG_SPLIT_NO_EMPTY);

            foreach ($keywords as $index => $keyword) {
                $key = sprintf('keyword%s', $index);
                $qb
                    ->andWhere(sprintf('NORMALIZE(p.name) LIKE NORMALIZE(:%s) OR
                        NORMALIZE(p.search_word) LIKE NORMALIZE(:%s) OR
                        NORMALIZE(p.operator_name) LIKE NORMALIZE(:%s) OR
                        EXISTS (SELECT wpc%d FROM \Eccube\Entity\ProductClass wpc%d
                        WHERE p = wpc%d.Product AND NORMALIZE(wpc%d.code) LIKE NORMALIZE(:%s))',
                        $key, $key, $key, $index, $index, $index, $index, $key
                    ))
                    ->setParameter($key, '%'.$keyword.'%');
            }
        }

        return $this->queries->customize(QueryKey::PRODUCT_SEARCH, $qb, $searchData);

    }

}
