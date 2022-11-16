<?php

namespace Customize\Entity;

#DBにアクセスするためのライブラリなどを読み込み
use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation as Eccube;
use Eccube\Entity\Customer;

#拡張をする対象エンティティの指定
/**
 * @Eccube\EntityExtension("Eccube\Entity\Customer")
 */
trait CustomerTrait //ファイル名と合わせる
{
    //ココに実際の拡張内容などを記述していきます


    /**
     * @ORM\Column(name="mail_magazine",type="boolean",nullable=false,options={"default":false})
     */
    public $mail_magazine;



    /**
     * Set lastBuyDate.
     *
     * @param boolean $mailMagazine
     *
     * @return Customer
     */
    public function setMailMagazine($mailMagazine = false)
    {
        $this->mail_magazine = $mailMagazine;

        return $this;
    }

    /**
     * Get mailMagazine.
     *
     * @return boolean
     */
    public function getMailMagazine()
    {
        return $this->mail_magazine;
    }

}
