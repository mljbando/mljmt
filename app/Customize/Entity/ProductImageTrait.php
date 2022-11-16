<?php

namespace Customize\Entity;


#DBにアクセスするためのライブラリなどを読み込み
use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation as Eccube;

#拡張をする対象エンティティの指定
/**
 * @Eccube\EntityExtension("Eccube\Entity\Product")
 */
trait ProductImageTrait //ファイル名と合わせる
{
    //ココに実際の拡張内容などを記述していきます



}