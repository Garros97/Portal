<?php
namespace App\Form;

use Cake\Form\Form;
use Cake\Form\Schema;
use Cake\Validation\Validator;

class SelectProjectForm extends Form //TODO: A form instance for this is maybe a bit overkill
{
    private $validProjects;

    public function __construct($validProjects)
    {
        $this->validProjects = $validProjects;
    }

    protected function _buildSchema(Schema $schema)
    {
        return $schema->addField('project', ['type' => 'string', 'length' => 70]);
    }

    protected function _buildValidator(Validator $validator)
    {
        return $validator->add('project', 'valid', [
            'rule' => ['inList', $this->validProjects],
            'message' => 'Das gewählte Projekt ist nicht gültig!'
        ]);
    }
}