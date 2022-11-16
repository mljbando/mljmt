<?php

namespace Customize\Entity;

#DBにアクセスするためのライブラリなどを読み込み
use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation as Eccube;

#拡張をする対象エンティティの指定
/**
 * @Eccube\EntityExtension("Eccube\Entity\ProductClass")
 */
trait ProductClassTrait //ファイル名と合わせる
{
    //ココに実際の拡張内容などを記述していきます

    /**
     * @ORM\Column(name="redhorse_management_code", type="string", length=255, nullable=true)
     * @Eccube\FormAppend(
     *  auto_render=true,
     *  options={
     *   "required": false,
     *   "label":"連携コード（規格）"
     *  }
     * )
     */
    public $redhorse_management_code;

}
