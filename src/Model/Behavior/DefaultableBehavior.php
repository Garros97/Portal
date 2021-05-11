<?php
namespace App\Model\Behavior;

use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Table;

/**
 * Defaultable behavior.
 *
 * This behavior can supply default values during saving
 * of new entities. This is especially useful for TEXT
 * columns, as MySQL does not support default values for
 * that. For everything else, a default value in the databse
 * is preferred.
 */
class DefaultableBehavior extends Behavior
{
    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];

    public function beforeSave(Event $event, EntityInterface $entity)
    {
        if (!$entity->isNew())
            return;

        foreach ($this->_config['defaults'] as $propName => $defaultVal) {
            if (!isset($entity->$propName)) {
                $entity->$propName = $defaultVal;
            }
        }
    }
}
