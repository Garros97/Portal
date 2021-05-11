<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * WakeningCallSubscriber Entity
 *
 * @property int $id
 * @property int $wakening_call_id
 * @property string $email
 *
 * @property \App\Model\Entity\WakeningCall $wakening_call
 */
class WakeningCallSubscriber extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'wakening_call_id' => true,
        'email' => true,
        'wakening_call' => true
    ];
}