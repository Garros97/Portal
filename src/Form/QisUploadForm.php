<?php
namespace App\Form;

use Cake\Core\Configure;
use Cake\Filesystem\Folder;
use Cake\Form\Form;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

class QisUploadForm extends Form
{
    protected function _buildSchema(Schema $schema)
    {
        return $schema->addField('qisfile', 'file');
    }

    protected function _buildValidator(Validator $validator)
    {
        return $validator->add('qisfile', 'file_valid', [
            'rule' => function($data, $context) {
                switch ($data['error']) {
                    case UPLOAD_ERR_OK:
                        return true;
                    case UPLOAD_ERR_INI_SIZE:
                        return sprintf('Die Datei war größer als die erlaubte Dateigröße (%d)', ini_get('upload_max_filesize'));
                    case UPLOAD_ERR_FORM_SIZE:
                        return 'Die Datei war größer als erlaubt';
                    case UPLOAD_ERR_PARTIAL:
                        return 'Die Datei wurde nur teilweise übertragen.';
                    case UPLOAD_ERR_NO_FILE:
                        return 'Es wurde keine Datei empfangen.';
                    case UPLOAD_ERR_NO_TMP_DIR:
                        return 'Interner Fehler: Kann Verzeichnis zu Speichern der Datei nicht finden';
                    case UPLOAD_ERR_CANT_WRITE:
                        return 'Interner Fehler: Kann Datei nicht speichern.';
                    case UPLOAD_ERR_EXTENSION:
                        return 'Interner Fehler: Upload durch PHP-Extension abgebrochen';
                    default:
                        return 'Unbekannter Fehler';
                }
            }
        ]);
    }

    protected function _execute(array $data)
    {
        if (Hash::check($data, 'qisfile.tmp_name')) {
            $uploadFolder = new Folder(ROOT . DS . Configure::read('App.uploads') . DS . 'qis_import', true, 0777);
            move_uploaded_file($data['qisfile']['tmp_name'], $uploadFolder->path . DS . $data['qisfile']['name']);
        }
        else return false;

        return true;
    }
}