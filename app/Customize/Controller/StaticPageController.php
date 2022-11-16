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

use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

class StaticPageController extends AbstractController
{
    /**
     * @Template("Static/about.twig")
     */
    public function about()
    {
        return ['name' => 'about'];
    }

    /**
     * @Template("Static/jigyousha.twig")
     */
    public function jigyousha()
    {
        return ['name' => 'jigyousha'];
    }

    /**
     * @Template("Static/use.twig")
     */
    public function use()
    {
        return ['name' => 'use'];
    }
}
