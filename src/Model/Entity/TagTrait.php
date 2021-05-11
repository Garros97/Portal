<?php
namespace App\Model\Entity;

use Cake\Collection\Collection;
use Cake\ORM\TableRegistry;

/**
 * A trait for entities that may have tags attached.
 * @package App\Model\Entity
 */
trait TagTrait
{
    /**
     * Returns the value for the tag with the given name.
     *
     * This method assumes that $this->tags is populated. (Use contain => Tags)
     *
     * @param string $tagName The name of the tag.
     * @param string $default The value that is returned if the tags is not found.
     * @return string|null The value or the default.
     */
    public function getTagValue($tagName, $default = null)
    {
        if ($this->tags === null) {
            return $default;
        }

        $tag = (new Collection($this->tags))->firstMatch(['name' => $tagName]);
        if ($tag === null || $tag->_joinData->value === null)
            return $default;
        else
            return $tag->_joinData->value;
    }

    /**
     * Checks whenever an entity has a specific tag.
     *
     * This method assumes that $this->tags is populated. (Use contain => Tags)
     *
     * @param string $tagName
     * @return boolean True when the entity has the tag, false otherwise.
     */
    public function hasTag($tagName)
    {
        return collection($this->tags ?: [])->firstMatch(['name' => $tagName]) !== null;
    }

    /**
     * Add a tag with the given name to the current entity if it does
     * not already exists. When it already exists, the value is changed
     * to the given value.
     *
     * @param string $tagName The name of the tag to add.
     * @param string|null $tagValue The value, or null for no value
     */
    public function addTag($tagName, $tagValue = null)
    {
        if ($this->isNew() && $this->tags === null) { //this can be the case for instances freshly from Table::newEntity()
            $this->tags = [];
        }

        $existingTag = collection($this->tags)->firstMatch(['name' => $tagName]);
        if ($existingTag !== null)
        {
            $existingTag->_joinData->value = $tagValue;
            $this->setDirty('tags', true);
            return;
        }

        //does not already exist
        $sourceTable = TableRegistry::get($this->getSource());
        $newTag = $sourceTable->Tags->findByName($tagName)->first();
        if ($newTag === null)
            $newTag = $sourceTable->Tags->newEntity(['name' => $tagName, '_joinData' => ['value' => $tagValue]]); //for some reason we cannot add the _joinData here...
        $newTag->_joinData = $sourceTable->Tags->junction()->newEntity(['value' => $tagValue]);
        $this->tags[] = $newTag;
        $this->setDirty('tags', true);
    }

    /**
     * Changes the value of a tag to a value. The tag has to exist.
     *
     * @param string $tagName The name of the tag.
     * @param string|null $tagValue The new value.
     */
    public function setTagValue($tagName, $tagValue)
    {
        $existingTag = (new Collection($this->tags))->firstMatch(['name' => $tagName]);
        $existingTag->_joinData->value = $tagValue;
        $this->setDirty('tags', true);
    }

    /**
     * Remove the tag with the given name from this entity if it exists.
     *
     * @param string $tagName The name of the tag.
     */
    public function removeTag($tagName)
    {
        $tag = collection($this->tags)->firstMatch(['name' => $tagName]);
        if ($tag === null)
            return; //not found

        $pos = array_search($tag, $this->tags);
        unset($this->tags[$pos]);
        $this->setDirty('tags', true);
    }

    /**
     * All tags whose names start with the given prefix are found,
     * and array of these names *with the prefix removed* is returned.
     *
     * This method is particular useful for "pseudo collection" with are
     * stored in tags with a common prefix, like exgroups.
     *
     * @param string $prefix The prefix
     * @return array The names
     */
    public function getTagNamesByPrefix($prefix)
    {
        return collection($this->tags)->sortBy('name', SORT_ASC, SORT_NATURAL)->filter(function($tag) use($prefix) {
            return strncmp($tag->name, $prefix, strlen($prefix)) === 0;
        })->map(function($tag) use ($prefix) {
            return substr($tag->name, strlen($prefix));
        })->toArray();
    }

    /**
     * Removes all tags with the given prefix.
     *
     * This method is particular useful for "pseudo collection" with are
     * stored in tags with a common prefix, like exgroups. Using this
     * method you can fist remove all tags with the prefix and then re-add
     * the current tags for syncing.
     *
     * TODO: Add a dedicated sync() method?
     *
     * @param string $prefix The prefix
     */
    public function removeTagsWithPrefix($prefix)
    {
        if ($this->tags === null) {
            return;
        }

        foreach ($this->tags as $tag) {
            if (strncmp($tag->name, $prefix, strlen($prefix)) === 0) {
                $this->removeTag($tag->name);
            }
        }
    }
}