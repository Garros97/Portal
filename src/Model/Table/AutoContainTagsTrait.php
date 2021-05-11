<?php
namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\Query;
use Cake\Utility\Hash;

trait AutoContainTagsTrait
{
    public function beforeFind(Event $event, Query $query, \ArrayObject $options, $primary)
    {
        if (!Hash::get($options, 'noAutoContainTags', false) && !Configure::read('noAutoLoad')) {
            //automatically contain Tags, as needed by TagsTrait (we virtually always access the tags, so lazy loading is bad)
            $query->contain('Tags');
        }
    }
}