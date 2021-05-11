<?php
namespace App\Model\Entity;

use Cake\Utility\Inflector;

trait TagAsFieldTrait
{
    public function &get($property)
    {
        $propName = Inflector::variable($property);
        $found = false;
        $default = null;

        if (in_array($propName, $this->_tagFields, true)) {
            $found = true;
        } elseif (isset($this->_tagFields[$propName])) {
            $default = $this->_tagFields[$propName];
            $found = true;
        }

        if ($found) {
            $tmp = $this->getTagValue($propName, $default); //need a variable here, because the return value is a reference
            return $tmp;
        } else {
            return parent::get($property);
        }
    }

    public function set($property, $value = null, array $options = [])
    {
        $props = $property; //keep old value

        if (is_string($property)) {
            $props = [$property => $value];
        }

        foreach ($props as $p => $v) {
            $propName = Inflector::variable($p);
            $found = false;
            $default = null;

            if (in_array($propName, $this->_tagFields, true)) {
                $found = true;
            } elseif (isset($this->_tagFields[$propName])) {
                $default = $this->_tagFields[$propName];
                $found = true;
            }

            if ($found) {
                //if (strlen(trim($v)) !== 0) {
                if ($v != $default) { //loose comparison!
                    $this->addTag($propName, $v);
                } else {
                    $this->removeTag($propName);
                }
            }
        }

        parent::set($property, $value, $options); //always call the real setter
    }
}