<?php
namespace App\Model\Entity;

use Cake\Core\Configure;
use InvalidArgumentException;

//This file defines two versions of the entity class based on the config.
//The debug version will warn if null is returned from get because the property
//was not set, the production version will do nothing.

if (Configure::read('debug') && Configure::read('Misc.warnOnMissingContain')) {
    class Entity extends \Cake\ORM\Entity
    {
        public function &get($property)
        {
            if (!strlen((string)$property)) {
                throw new InvalidArgumentException('Cannot get an empty property');
            }

            $value = null;
            $method = $this->_accessor($property, 'get');

            if (isset($this->_properties[$property])) {
                $value =& $this->_properties[$property];
            }

            if ($method) {
                $result = $this->{$method}($value);

                return $result;
            }

            if (!$this->isNew() && !array_key_exists($property, $this->_properties) && !in_array($property, ['_method', '_unused'])) {
                trigger_error("Read on unset property $property");
            }

            return $value;
        }
    }
}
else {
    class Entity extends \Cake\ORM\Entity {

    }
}