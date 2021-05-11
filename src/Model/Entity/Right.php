<?php
namespace App\Model\Entity;

use Cake\ORM\TableRegistry;

/**
 * Right Entity
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property bool $supports_subresources
 *
 * @property \App\Model\Entity\User[] $users
 */
class Right extends Entity
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
        '*' => true,
        'id' => false
    ];

    public function getNameWithSubresource()
    {
        $subresource = $this->_joinData->subresource; //map rights to the form "RIGHT/123" TODO: It is easier to store this in an assoc array or so?
        if ($subresource === 0) {
            $subresource = '';
        } else {
            $subresource = '/' . $subresource;
        }
        return $this->name . $subresource;
    }

    public function getHumanReadableSubresource()
    {
        $subresource = $this->_joinData->subresource;
        if ($subresource === 0) {
            return '-';
        } else {
            switch ($this->name) {
                case 'MANAGE_PROJECTS':
                case 'RATE':
                    //map the ID to a human-readable name
                    $project = TableRegistry::get('Projects')->findById($subresource)->first();
                    if (!$project) {
                        return "<i>???</i> [PID $subresource]";
                    } else {
                        return "{$project->name} [PID {$project->id}]";
                    }
                default:
                    return $subresource; //just the name of the subresource
            }
        }
    }
}
