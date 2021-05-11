<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Routing\Router;

/**
 * Form Entity
 *
 * @property int $id
 * @property string $title
 * @property string $urlname
 * @property int $state
 * @property string $description
 * @property string $header_image
 */
class Form extends Entity
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
        'title' => true,
        'urlname' => true,
        'state' => true,
        'description' => true,
        'header_image' => true
    ];

    public function isOnline()
    {
        return ($this->state == 0); //TODO
    }

    public function getUrl()
    {
        $url = parse_url(Router::url('/', true));
        return $url; //"{$url['scheme']}://{$url['host']}/weckruf/?name={$this->urlname}"; //TODO
    }
}

