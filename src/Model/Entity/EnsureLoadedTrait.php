<?php
namespace App\Model\Entity;

use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;

/**
 * EnsureLoaded trait
 *
 * Provides an ensureLoaded() method for lazy loading associations
 */
trait EnsureLoadedTrait
{
    public function ensureLoaded($name)
    {
        /** @var $this \Cake\ORM\Entity */
        $camelCaseName = Inflector::camelize($name);
        $varName = Inflector::underscore($name);

        if (array_key_exists($varName, $this->_properties))
            return $this->_properties[$varName];
        TableRegistry::get($this->getSource())->loadInto($this, [$camelCaseName]);
        return $this->_properties[$varName];
    }
}
