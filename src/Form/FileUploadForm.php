<?php
namespace App\Form;

use Cake\Core\Configure;
use Cake\Filesystem\Folder;
use Cake\Form\Form;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use App\Model\Entity\UploadedFile;

class FileUploadForm extends Form
{
    protected $_fileData;

    protected function _buildSchema(Schema $schema)
    {
        return $schema->addField('file', 'file');
    }

    protected function _buildValidator(Validator $validator)
    {
        return $validator->add('file', 'file_valid', [
            'rule' => function($data, $context) {
                switch ($data['error']) {
                    case UPLOAD_ERR_OK:
                        if (array_key_exists('appendix', $data) && $data['type'] !== 'application/pdf') // only check for wrong extension if there are no other errors, because otherwise the file extension is empty
                            return 'Upload fehlgeschlagen: Es können nur PDF-Dateien als Anhang hinzugefügt werden.';
                        return true;
                    case UPLOAD_ERR_INI_SIZE:
                        return 'Die Datei war größer als die erlaubte Dateigröße ' . ini_get('upload_max_filesize') . 'B';
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
        if (Hash::check($data, 'file.tmp_name')) {
            $appendix = Hash::check($data, 'file.appendix');
            $this->_fileData = $data['file'];
            $this->_fileData['uniqid'] = uniqid('upload_');
            $uploadFolder = new Folder(ROOT . DS . Configure::read('App.uploads') . DS . ($appendix ? 'confirmation_appendices' : 'user_uploads'), true, 0777); // save appendix files in a separate subfolder
            move_uploaded_file($data['file']['tmp_name'], $uploadFolder->path . DS . $this->_fileData['uniqid']);
        }
        else
            return false;

        return true;
    }

    /**
     * Patches an entity to include the details about
     * the the file uploaded with this form.
     *
     * @param UploadedFile $entity
     * @return UploadedFile
     */
    public function patchEntity($entity)
    {
        $entity->original_filename = $this->_fileData['name'];
        $entity->mime_type = $this->_fileData['type'];
        $entity->disk_filename = $this->_fileData['uniqid'];
    }

    /**
     * Returns filename to be saved in appendix tag (appendix files are not saved in database)
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->_fileData['uniqid'];
    }
}