<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

/**
 * Courses Controller
 *
 * @property \App\Model\Table\CoursesTable $Courses
 */
class CoursesController extends TagAwareController
{
    public $rights = [
        'edit' => ['MANAGE_PROJECTS/?'],
        'delete' => ['MANAGE_PROJECTS/?'],
        'deleteTag' => ['MANAGE_PROJECTS/?', 'EDIT_TAGS'],
        'addScales' => ['MANAGE_PROJECTS/?']
    ];

    public function getRequiredSubresourceIds($right, $request)
    {
        return $this->Courses->get($request->getParam('pass')[0])->project_id;
    }

    /**
     * Edit method
     *
     * @param string|null $id Course id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws Cake\Http\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $course = $this->Courses->get($id, [
            'contain' => ['Scales', 'Projects']
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $this->_handleNewTagRequest($course);

            $course = $this->Courses->patchEntity($course, $this->request->getData());

            if (!$course->register_end_active) {
                $course->register_end = null;
            }

            if ($course->isDirty('max_users')) {
                $old = $course->getOriginal('max_users');
                $new = $course->max_users;
                $registeredCount = $course->registration_count;
                if ($old < $new || $new == 0) { //increased
                    if ($registeredCount > $old && $old != 0) {
                        $this->Flash->warning('<b>Achtung</b>: Es sind durch Ihre Änderung Teilnehmer von der Warteliste nachgerückt. Diese wurden <b>nicht</b> automatisch benachrichtigt!', ['escape' => false]);
                    }
                }
                if ($old > $new || $old == 0) { //reduced
                    if ($registeredCount > $new && $new != 0) {
                        $this->Flash->warning('<b>Achtung</b>: Es wurden durch Ihre Änderung Teilnehmer auf die Warteliste gesetzt. Diese wurden <b>nicht</b> automatisch benachrichtigt!', ['escape' => false]);
                    }
                } else {
                    //cannot happen :)
                }
            }

            if ($this->Courses->save($course)) {
                $this->Flash->success('Der Kurs wurde gespeichert!');
            } else {
                $this->Flash->error('Der Kurs konnte nicht gespeichert werden. Bitte versuchen Sie es erneut.');
            }
        }

        $usedExgroups = $this->_getUsedListElements('exgroup', $course);
        $usedFilters = $this->_getUsedListElements('filter', $course);


        $tags = $this->Users->Tags->find()->innerJoinWith('Courses')->distinct()->extract('name');
        $this->set(compact('course', 'usedExgroups', 'usedFilters', 'tags'));
    }

    protected function _getUsedListElements($name, $course)
    {
        $usedElems = $this->Courses->Tags->find()->innerJoinWith('Courses', function ($q) {
            return $q->applyOptions(['noLoadRegistrationCount' => true]);
        })->innerJoinWith('Courses.Projects', function ($q) use ($course) {
            return $q->where(['Projects.id' => $course->project_id]);
        })->where(['Tags.name LIKE' => $name . '\\_%'])->select('name')->distinct()->sortBy('name', SORT_ASC, SORT_NATURAL)->map(function ($tag) use ($name) {
            return substr($tag->name, strlen($name . '_'));
        })->toArray();

        return $usedElems;
    }

    public function addScales($id = null)
    {
        $this->request->allowMethod(['post', 'put']);
        if (!$this->Courses->get($id, ['contain' => 'Projects'])->project->requiresGroupRegistration()) {
            throw new \LogicException('Can\'t add scales to non-group-projects!');
        }

        $count = $this->request->getData('add-scale-count');
        $scalesTable = TableRegistry::get('Scales');

        for ($i = 1; $i <= $count; $i++) { //this loops runs from 1 to count + 1 for better names
            $scale = $scalesTable->newEntity([
                'name' => "Neue Skala $i",
                'course_id' => $id,
                'hint' => '',
                'user_visible' => false,
            ]);
            $scalesTable->save($scale);
        }

        $msg = $count . ($count == 1 ? ' neue Skala' : ' neue Skalen');
        $this->Flash->success("$msg wurden angelegt, Sie können diese nun bearbeiten.");
        return $this->redirect(['action' => 'edit', $id]);
    }

    /**
     * Delete method
     *
     * @param string|null $id Course id.
     * @return void Redirects to index.
     * @throws Cake\Http\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $course = $this->Courses->get($id);
        if ($this->Courses->delete($course)) {
            $this->Flash->success('Der Kurs wurde gelöscht.');
        } else {
            $this->Flash->error('Der Kurs konnte nicht gelöscht werden, versuchen Sie es erneut.');
        }
        return $this->redirect(['controller' => 'projects', 'action' => 'edit', $course->project_id]);
    }
}
