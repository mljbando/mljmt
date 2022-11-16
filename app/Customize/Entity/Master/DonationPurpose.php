<?php


namespace Customize\Entity\Master;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\Master\AbstractMasterEntity;

/**
 * DonationPurposeRepository
 *
 * @ORM\Table(name="mtb_donation_purpose")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="Customize\Repository\Master\DonationPurposeRepository")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class DonationPurpose extends AbstractMasterEntity
{

}
