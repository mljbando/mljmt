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

namespace Customize\Controller;

use Customize\Form\Type\SearchProductType;
use Customize\Repository\ProductRepository;
use Eccube\Controller\AbstractController;
use Eccube\Entity\Master\ProductStatus;
use Eccube\Entity\Product;
use Eccube\Entity\ProductClass;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Repository\CustomerFavoriteProductRepository;
use Eccube\Repository\ProductClassRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FrontApiController extends AbstractController
{
    /**
     * @var CustomerFavoriteProductRepository
     */
    private $customerFavoriteProductRepository;

    public function __construct(
        ProductClassRepository $productClassRepository,
        CustomerFavoriteProductRepository $customerFavoriteProductRepository,
        ProductRepository $productRepository
    ) {
        $this->productClassRepository = $productClassRepository;
        $this->productRepository = $productRepository;
        $this->customerFavoriteProductRepository = $customerFavoriteProductRepository;
    }

    /**
     * 検索中の商品数取得
     *
     * @method("get")
     * @\Symfony\Component\Routing\Annotation\Route("/api/product_count")
     */
    public function getSearchProductCount(Request $request)
    {
        $count = 0;

        $builder = $this->formFactory->createNamedBuilder('', SearchProductType::class);
        if ($request->getMethod() === 'GET') {
            $builder->setMethod('GET');
        }
        $searchForm = $builder->getForm();

        $searchForm->handleRequest($request);

        $searchData = $searchForm->getData();
        $qb = $this->productRepository->getQueryBuilderBySearchDataForCount($searchData);

        $event = new EventArgs(
            [
                'searchData' => $searchData,
                'qb' => $qb,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_PRODUCT_INDEX_SEARCH, $event);
        $searchData = $event->getArgument('searchData');

        return new Response(
            json_encode([
                'count' => $qb->getQuery()->getSingleResult()['cnt'] ?? 0,
            ], JSON_THROW_ON_ERROR),
            Response::HTTP_OK,
            ['Content-Type' => 'text/plain; charset=utf-8']
        );
    }

    /**
     * @method("get")
     * @\Symfony\Component\Routing\Annotation\Route("/api/price_range")
     */
    public function getPriceRange()
    {
        $defaultStep = 5000;
        // get max price
        $productMax = $this->productClassRepository
            ->createQueryBuilder('c')
            ->setMaxResults(1)
            ->where('c.visible = 1')
            ->orderBy('c.price01', 'desc')
            ;

        $query = $productMax->getQuery();

        $steps = range(0, $query->getSingleResult()->getPrice01(), $defaultStep);
        //  count each price products

        $productCountQuery = $this->countRageProducts($steps);
        $count = [];
        $productCountEntity = $productCountQuery->getSingleResult();
        foreach ($steps as $value) {
            $count[] = $productCountEntity['pr_'.$value] ?? 0;
        }

        return new Response(
            json_encode([
                'data_source' => $count,
                'label_source' => $steps,
                'max_price' => $query->getSingleResult()->getPrice01(),
            ], JSON_THROW_ON_ERROR),
            Response::HTTP_OK,
            ['Content-Type' => 'text/plain; charset=utf-8']
        );
    }

    private function countRageProducts($steps = [0])
    {
        $productCount = $this->entityManager
            ->getRepository(ProductClass::class) // private でフィールド格納されるのを回避
            ->createQueryBuilder('c')
            ->setMaxResults(1)
            ->where('c.visible=1')
            ->select('count(c.id) as cnt')
            ;

        $prev = 0;
        foreach ($steps as $price) {
            if ($price) {
                $productCount
                    ->addSelect(
                        sprintf(
                            'SUM(case when c.price01 >= %s AND c.price01 < %s then 1 else 0 end) as pr_%s',
                            $prev,
                            $price,
                            $prev
                        )
                    );
            }
            $prev = $price;
        }
        $productCount
            ->addSelect('SUM(case when c.price01 >= '.$prev.' then 1 else 0 end) as pr_'.$prev);

        return $productCount->getQuery();
    }

    /**
     * お気に入り追加.
     *
     * @Route("/api/add_favorite/{id}", name="api_product_add_favorite", requirements={"id" = "\d+"}, methods={"GET", "POST"})
     */
    public function addFavorite(Request $request, Product $Product)
    {
        $this->checkVisibility($Product);

        $event = new EventArgs(
            [
                'Product' => $Product,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_PRODUCT_FAVORITE_ADD_INITIALIZE, $event);

        if ($this->isGranted('ROLE_USER')) {
            $Customer = $this->getUser();
            $this->customerFavoriteProductRepository->addFavorite($Customer, $Product);
            $this->session->getFlashBag()->set('product_detail.just_added_favorite', $Product->getId());

            $event = new EventArgs(
                [
                    'Product' => $Product,
                ],
                $request
            );
            $this->eventDispatcher->dispatch(EccubeEvents::FRONT_PRODUCT_FAVORITE_ADD_COMPLETE, $event);

            return new Response(
                json_encode([
                    'message' => 'ok',
                 ], JSON_THROW_ON_ERROR),
                Response::HTTP_OK,
                ['Content-Type' => 'text/plain; charset=utf-8']
            );
        } else {
            // 非会員の場合、ログイン画面を表示
            //  ログイン後の画面遷移先を設定
            $this->setLoginTargetPath($this->generateUrl('product_add_favorite', ['id' => $Product->getId()], UrlGeneratorInterface::ABSOLUTE_URL));
            $this->session->getFlashBag()->set('eccube.add.favorite', true);

            $event = new EventArgs(
                [
                    'Product' => $Product,
                ],
                $request
            );
            $this->eventDispatcher->dispatch(EccubeEvents::FRONT_PRODUCT_FAVORITE_ADD_COMPLETE, $event);

            return new Response(
                json_encode([
                    'message' => 'not authenticate',
                    'locate' => $this->generateUrl(
                        'product_add_favorite',
                        ['id' => $Product->getId()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                ], JSON_THROW_ON_ERROR),
                Response::HTTP_NOT_ACCEPTABLE,
                ['Content-Type' => 'text/plain; charset=utf-8']
            );
        }
    }

    /**
     * 閲覧可能な商品かどうかを判定
     *
     * @param Product $Product
     *
     * @return boolean 閲覧可能な場合はtrue
     *
     * @see ProductController
     */
    protected function checkVisibility(Product $Product)
    {
        $is_admin = $this->session->has('_security_admin');

        // 管理ユーザの場合はステータスやオプションにかかわらず閲覧可能.
        if (!$is_admin) {
            // 在庫なし商品の非表示オプションが有効な場合.
            // if ($this->BaseInfo->isOptionNostockHidden()) {
            //     if (!$Product->getStockFind()) {
            //         return false;
            //     }
            // }
            // 公開ステータスでない商品は表示しない.
            if ($Product->getStatus()->getId() !== ProductStatus::DISPLAY_SHOW) {
                return false;
            }

            if ($Product->out_of_print) {
                return false;
            }

            if ($Product->published_at_from > new \DateTime()) {
                return false;
            }

            if ($Product->published_at_to <= new \DateTime()) {
                return false;
            }

            if ($Product->delivered_on_from > new \DateTime()) {
                return false;
            }

            if ($Product->delivered_on_to <= new \DateTime()) {
                return false;
            }
        }

        return true;
    }
}
