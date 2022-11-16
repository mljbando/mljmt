<?php

namespace Customize\Entity;

#DBにアクセスするためのライブラリなどを読み込み
use Customize\Entity\Master\DonationPurpose;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation as Eccube;
use Eccube\Entity\Order;

#拡張をする対象エンティティの指定
/**
 * @Eccube\EntityExtension("Eccube\Entity\Order")
 */
trait OrderTrait //ファイル名と合わせる
{
    //ココに実際の拡張内容などを記述していきます

    /**
     * @var DonationPurpose
     *
     * @ORM\ManyToOne(targetEntity="Customize\Entity\Master\DonationPurpose")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="donation_purpose_id", referencedColumnName="id")
     * })
     */
    private $DonationPurpose;

    /**
     * @ORM\Column(name="one_stop",type="boolean",nullable=true,options={"default":false})
     */
    public $one_stop;

    /**
     * @ORM\Column(name="name_announce",type="boolean",nullable=true,options={"default":false})
     */
    public $name_announce;


    /**
     * @ORM\Column(name="cheer_message_publish",type="boolean",nullable=true,options={"default":false})
     */
    public $cheer_message_publish;

    /**
     * @ORM\Column(name="cheer_message",type="text",nullable=true)
     */
    public $cheer_message;

    /**
     * @ORM\Column(name="offer_memo",type="text",nullable=true)
     */
    public $offer_memo;


    /**
     * @ORM\Column(name="document_send_difference",type="boolean",nullable=true,options={"default":false})
     * @Eccube\FormAppend(
     *     auto_render=false,
     *     type="\Symfony\Component\Form\Extension\Core\Type\CheckboxType",
     *     options={
     *          "required": false,
     *          "label": "異なる住所に送付",
     *     })
     */
    public $document_send_difference;

    /**
     * @var string|null
     *
     * @ORM\Column(name="doc_name01", type="string", length=255, nullable=true)
     */
    public $doc_name01;

    /**
     * @var string|null
     *
     * @ORM\Column(name="doc_name02", type="string", length=255, nullable=true)
     */
    public $doc_name02;

    /**
     * @var string|null
     *
     * @ORM\Column(name="doc_kana01", type="string", length=255, nullable=true)
     */
    public $doc_kana01;

    /**
     * @var string|null
     *
     * @ORM\Column(name="doc_kana02", type="string", length=255, nullable=true)
     */
    public $doc_kana02;

    /**
     * @var string|null
     *
     * @ORM\Column(name="doc_postal_code", type="string", length=8, nullable=true)
     */
    public $doc_postal_code;


    /**
     * @var string|null
     *
     * @ORM\Column(name="doc_addr01", type="string", length=255, nullable=true)
     */
    public $doc_addr01;

    /**
     * @var string|null
     *
     * @ORM\Column(name="doc_addr02", type="string", length=255, nullable=true)
     */
    public $doc_addr02;


    /**
     * @ORM\Column(name="agreement",type="boolean",nullable=true,options={"default":false})
     */
    public $agreement;


    /**
     * Set DonationPurpose.
     *
     * @param DonationPurpose|null $donation_purpose
     *
     * @return OrderTrait
     */
    public function setDonationPurpose(DonationPurpose $donation_purpose = null)
    {
        $this->DonationPurpose = $donation_purpose;

        return $this;
    }

    /**
     * Get DonationPurpose.
     *
     * @return DonationPurpose|null
     */
    public function getDonationPurpose()
    {
        return $this->DonationPurpose;
    }

}
