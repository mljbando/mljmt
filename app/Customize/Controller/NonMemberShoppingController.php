<?php

namespace Customize\Controller;

use Eccube\Controller\AbstractShoppingController;
use Symfony\Component\Routing\Annotation\Route;

class NonMemberShoppingController extends AbstractShoppingController
{
    /**
     * 非会員処理
     *
     * @Route("/shopping/nonmember", name="shopping_nonmember")
     * @Route("/shopping/nonmember/shipping/{id}", name="shopping_nonmember_shipping", requirements={"id" = "\d+"})
     */
    public function index()
    {
        return $this->redirectToRoute('homepage');
    }

    /**
     * お客様情報の変更(非会員)
     *
     * @Route("/shopping/customer", name="shopping_customer")
     */
    public function customer()
    {
        return $this->redirectToRoute('homepage');
    }
}
