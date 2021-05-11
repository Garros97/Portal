<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Routing\Router;

/**
 * WakeningCall Entity
 *
 * @property int $id
 * @property string $name
 * @property string $urlname
 * @property int $state
 * @property bool $permanent
 * @property string $email_from
 * @property string $email_subject
 * @property string $message
 *
 * @property \App\Model\Entity\WakeningCallSubscriber[] $wakening_call_subscribers
 */
class WakeningCall extends Entity
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
        'name' => true,
        'urlname' => false,
        'state' => true,
        'permanent' => true,
        'email_from' => true,
        'email_subject' => true,
        'message' => true,
        'wakening_call_subscribers' => true // is this reasonable?
    ];

    public function isHidden()
    {
        return ($this->state == 1);
    }

    public function isActive()
    {
        return ($this->state == 0);
    }

    public function isClosed()
    {
        return ($this->state == 2);
    }

    public function toggleVisibility()
    {
        if ($this->isHidden()) {
            $this->state = 0;
        } else if ($this->isActive()) {
            $this->state = 2;
        }
    }

    public function isComplete()
    {
        return (!empty($this->email_from) && !empty($this->email_subject) && !empty(trim($this->message)));
    }

    public function isPermanent()
    {
     return $this->permanent;
    }

    public function getUrl()
    {
        $url = parse_url(Router::url('/', true));
        return "{$url['scheme']}://{$url['host']}/weckruf/?name={$this->urlname}";
    }

    public function isSent()
    {
        return ($this->state == 3);
    }

    public function setSent()
    {
        $this->state = 3;
    }
}