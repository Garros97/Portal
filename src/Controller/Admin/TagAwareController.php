<?php
namespace App\Controller\Admin;

use App\Controller\AppController;

/**
 * Tag aware controller.
 *
 * This class an be used as a base class for controllers which interact with
 * tags.
 */
class TagAwareController extends AppController
{
    /**
     * @param $entity \App\Model\Entity\TagTrait; The entity to the tag to.
     */
    protected function _handleNewTagRequest($entity)
    {
        $newTagStr = $this->request->getData('newTag');
        if ($newTagStr) {
            $name = $newTagStr;
            $val = null;
            if (strpos($newTagStr, ':') !== false) {
                list($name, $val) = explode(':', $this->request->getData('newTag'), 2);
            }
            $entity->addTag($name, $val);
        }
        $this->request = $this->request->withData('newTag', null);
    }

    public function deleteTag($entityId, $tagName)
    {
        $entity = $this->{$this->modelClass}->get($entityId);
        $entity->removeTag($tagName);
        if ($this->{$this->modelClass}->save($entity)) {
            $this->Flash->success('Der Tag wurde entfernt');
        }
        else {
            $this->Flash->error('Der Tag konnte nicht entfernt werden, bitte versuchen Sie es erneut.');
        }
        return $this->redirect($this->referer());
    }
}