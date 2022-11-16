<?php

namespace Customize\Entity;

#DBにアクセスするためのライブラリなどを読み込み
use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation as Eccube;

#拡張をする対象エンティティの指定
/**
 * @Eccube\EntityExtension("Eccube\Entity\Product")
 */
trait ProductTrait //ファイル名と合わせる
{
    //ココに実際の拡張内容などを記述していきます
    /**
     * @ORM\Column(name="redhorse_management_code", type="string", length=255, nullable=true)
     * @Eccube\FormAppend(
     *  auto_render=true,
     *  options={
     *   "required": false,
     *   "label":"連携コード（返礼品）"
     *  }
     * )
     */
    public $redhorse_management_code;

    /**
     * @ORM\Column(name="municipality_code", type="string", length=255, nullable=true)
     * @Eccube\FormAppend(
     *  auto_render=true,
     *  options={
     *   "required": false,
     *   "label":"自治体コード"
     *  }
     * )
     */
    public $municipality_code;

    /**
     * @ORM\Column(name="operator_name", type="string", length=255, nullable=true)
     * @Eccube\FormAppend(
     *  auto_render=true,
     *  options={
     *   "required": false,
     *   "label":"事業者"
     *  }
     * )
     */
    public $operator_name;

    /**
     * @ORM\Column(name="division", type="smallint", nullable=true)
     * @Eccube\FormAppend(
     *  auto_render=false,
     *  options={
     *   "required": false,
     *   "label":"division"
     *  }
     * )
     */
    public $division;

    /**
     * @ORM\Column(name="capacity", type="text", nullable=true)
     * @Eccube\FormAppend(
     *  auto_render=true,
     *  options={
     *   "required": false,
     *   "label":"容量"
     *  }
     * )
     */
    public $capacity;

    /**
     * @ORM\Column(name="notes", type="text", nullable=true)
     * @Eccube\FormAppend(
     *  auto_render=true,
     *  options={
     *   "required": false,
     *   "label":"注意事項／その他"
     *  }
     * )
     */
    public $notes;

    /**
     * @ORM\Column(name="applicable_on_from", type="datetimetz", options={"default": null}, nullable=true)
     * @Eccube\FormAppend(
     *  auto_render=true,
     *  options={
     *   "required": false,
     *   "label":"システム判定用 申込期間(FROM)"
     *  }
     * )
     */
    public $applicable_on_from;

    /**
     * @ORM\Column(name="applicable_on_to", type="datetimetz", options={"default": null}, nullable=true)
     * @Eccube\FormAppend(
     *  auto_render=true,
     *  options={
     *   "required": false,
     *   "label":"システム判定用 申込期間(TO)"
     *  }
     * )
     */
    public $applicable_on_to;

    /**
     * @ORM\Column(name="delivered_on_from", type="datetimetz", options={"default": null}, nullable=true)
     * @Eccube\FormAppend(
     *  auto_render=true,
     *  options={
     *   "required": false,
     *   "label":"システム判定用 配送期間(FROM)"
     *  }
     * )
     */
    public $delivered_on_from;

    /**
     * @ORM\Column(name="delivered_on_to", type="datetimetz", options={"default": null}, nullable=true)
     * @Eccube\FormAppend(
     *  auto_render=true,
     *  options={
     *   "required": false,
     *   "label":"システム判定用 配送期間(TO)"
     *  }
     * )
     */
    public $delivered_on_to;

    /**
     * @ORM\Column(name="accepts_desired_deliver_date", type="boolean", nullable=false)
     * @Eccube\FormAppend(
     *  auto_render=true,
     *  options={
     *   "required": false,
     *   "label":"配送日指定可否"
     *  }
     * )
     */
    public $accepts_desired_deliver_date;

    /**
     * @ORM\Column(name="ng_remote_island", type="boolean", nullable=false)
     * @Eccube\FormAppend(
     *  auto_render=true,
     *  options={
     *   "required": false,
     *   "label":"離島フラグ"
     *  }
     * )
     */
    public $ng_remote_island;

    /**
     * @ORM\Column(name="undeliverable_prefectures", type="text", nullable=true)
     * @Eccube\FormAppend(
     *  auto_render=true,
     *  options={
     *   "required": false,
     *   "label":"配送不可都道府県"
     *  }
     * )
     */
    public $undeliverable_prefectures;

    /**
     * @ORM\Column(name="temperature_zone", type="text", nullable=true)
     * @Eccube\FormAppend(
     *  auto_render=true,
     *  options={
     *   "required": false,
     *   "label":"温度帯"
     *  }
     * )
     */
    public $temperature_zone;

    /**
     * @ORM\Column(name="visible", type="boolean", nullable=false)
     * @Eccube\FormAppend(
     *  auto_render=false,
     *  options={
     *   "required": false,
     *   "label":"公開"
     *  }
     * )
     */
    public $visible;

    /**
     * @ORM\Column(name="published_at_from", type="datetimetz", options={"default": null}, nullable=true)
     * @Eccube\FormAppend(
     *  auto_render=true,
     *  options={
     *   "required": false,
     *   "label":"公開開始日時"
     *  }
     * )
     */
    public $published_at_from;

    /**
     * @ORM\Column(name="published_at_to", type="datetimetz", options={"default": null}, nullable=true)
     * @Eccube\FormAppend(
     *  auto_render=true,
     *  options={
     *   "required": false,
     *   "label":"公開終了日時"
     *  }
     * )
     */
    public $published_at_to;

    /**
     * @ORM\Column(name="out_of_print", type="boolean", nullable=false)
     * @Eccube\FormAppend(
     *  auto_render=true,
     *  options={
     *   "required": false,
     *   "label":"廃盤フラグ"
     *  }
     * )
     */
    public $out_of_print;

    /**
     * @ORM\Column(name="out_of_stock", type="boolean", nullable=false)
     * @Eccube\FormAppend(
     *  auto_render=true,
     *  options={
     *   "required": false,
     *   "label":"品切れフラグ"
     *  }
     * )
     */
    public $out_of_stock;

    /**
     * @ORM\Column(name="only_credit", type="boolean", nullable=false)
     * @Eccube\FormAppend(
     *  auto_render=true,
     *  options={
     *   "required": false,
     *   "label":"クレジット限定"
     *  }
     * )
     */
    public $only_credit;

    /**
     * @ORM\Column(name="allergy_labeling", type="text", nullable=true)
     * @Eccube\FormAppend(
     *  auto_render=true,
     *  options={
     *   "required": false,
     *   "label":"アレルギー"
     *  }
     * )
     */
    public $allergy_labeling;

    /**
     * @ORM\Column(name="expiration_date", type="text", nullable=true)
     * @Eccube\FormAppend(
     *  auto_render=true,
     *  options={
     *   "required": false,
     *   "label":"消費期限"
     *  }
     * )
     */
    public $expiration_date;

    /**
     * @ORM\Column(name="room_temperature_delivery", type="boolean", nullable=false)
     * @Eccube\FormAppend(
     *  auto_render=true,
     *  options={
     *   "required": false,
     *   "label":"常温配送"
     *  }
     * )
     */
    public $room_temperature_delivery;

    /**
     * @ORM\Column(name="refrigerated_delivery", type="boolean", nullable=false)
     * @Eccube\FormAppend(
     *  auto_render=true,
     *  options={
     *   "required": false,
     *   "label":"冷蔵配送"
     *  }
     * )
     */
    public $refrigerated_delivery;

    /**
     * @ORM\Column(name="frozen_delivery", type="boolean", nullable=false)
     * @Eccube\FormAppend(
     *  auto_render=true,
     *  options={
     *   "required": false,
     *   "label":"冷凍配送"
     *  }
     * )
     */
    public $frozen_delivery;

    /**
     * @ORM\Column(name="applicable_on", type="text", nullable=true)
     * @Eccube\FormAppend(
     *  auto_render=true,
     *  options={
     *   "required": false,
     *   "label":"申込期日"
     *  }
     * )
     */
    public $applicable_on;

    /**
     * @ORM\Column(name="delivered_on", type="text", nullable=true)
     * @Eccube\FormAppend(
     *  auto_render=true,
     *  options={
     *   "required": false,
     *   "label":"配送期日"
     *  }
     * )
     */
    public $delivered_on;

}
