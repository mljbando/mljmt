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

namespace Customize\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;

/**
 * テストテーブル
 *
 * @ORM\Table(name="dtb_batch_history", options={"comment":"バッチ履歴"})
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="Customize\Repository\BatchHistoryRepository")
 */
class BatchHistory extends AbstractEntity
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="smallint", options={"unsigned":true, "default": 0})
     */
    private $status;


    /**
     * @var int
     *
     * @ORM\Column(name="process_time", type="integer", options={"unsigned":true, "default": 0})
     */
    private $process_time;

    /**
     * @var int
     *
     * @ORM\Column(name="total_count", type="integer", options={"unsigned":true, "default": 0})
     */
    private $total_count;

    /**
     * @var int
     *
     * @ORM\Column(name="affected_count", type="integer", options={"unsigned":true, "default": 0})
     */
    private $affected_count;

    /**
     * @var string
     *
     * @ORM\Column(name="call_url", type="text", nullable=true)
     */
    private $call_url;
    /**
     * @var string
     *
     * @ORM\Column(name="params", type="text", nullable=true)
     */
    private $params;

    /**
     * @var string
     *
     * @ORM\Column(name="error", type="text", nullable=true)
     */
    private $error;

    /**
     * @var string
     *
     * @ORM\Column(name="batch_id", type="string")
     */
    private $batch_id;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="create_date", type="datetimetz")
     */
    private $create_date;



    /**
     * @var DateTime
     *
     * @ORM\Column(name="update_date", type="datetimetz")
     */
    private $update_date;

    /**
     * Set id.
     *
     * @param int $id
     *
     * @return BatchHistory
     */
    public function setId(int $id): BatchHistory
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set status.
     *
     * @param int $status
     *
     * @return BatchHistory
     */
    public function setStatus(int $status): BatchHistory
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

}
