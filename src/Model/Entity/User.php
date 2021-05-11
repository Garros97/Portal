<?php
namespace App\Model\Entity;

use Cake\Auth\DefaultPasswordHasher;
use Cake\Core\Configure;
use Cake\Log\LogTrait;
use Cake\Mailer\MailerAwareTrait;
use Cake\ORM\TableRegistry;
use Psr\Log\LogLevel;
use PWGen\PWGen;

/**
 * User Entity
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $first_name
 * @property string $last_name
 * @property string $sex (m = male, f = female, x = other/unknown)
 * @property string $street
 * @property string $house_number
 * @property string $postal_code
 * @property string $city
 * @property bool $is_teacher
 * @property string $full_name
 * @property string $greeting
 * @property \Cake\I18n\Time $birthday
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 * @property \Cake\I18n\Time $last_login
 *
 * @property \App\Model\Entity\Registration[] $registrations
 * @property \App\Model\Entity\UploadedFile[] $uploaded_files
 * @property \App\Model\Entity\Group[] $groups
 * @property \App\Model\Entity\Right[] $rights
 * @property \App\Model\Entity\Tag[] $tags
 */
class User extends Entity
{
    use TagTrait;
    use EnsureLoadedTrait;
    use MailerAwareTrait;
    use LogTrait;

    private static $_pwgenInstance = null;

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
        'password' => false,
        'last_login' => false,
        'rights' => false
    ];

    protected function _getPasswordHasher()
    {
        return new DefaultPasswordHasher();
    }

    protected function _setPassword($value)
    {
        $hasher = $this->_getPasswordHasher();
        return $hasher->hash($value);
    }

    protected function _getIsTeacher()
    {
        if (isset($this->_properties['is_teacher']))
            return $this->_properties['is_teacher'];

        return $this->hasTag('isTeacher');
    }

    protected function _setIsTeacher($value)
    {
        if ($value)
            $this->addTag('isTeacher');
        else
            $this->removeTag('isTeacher');
        return $value;
    }

    protected function _getFullName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    protected function _getGreeting()
    {
        switch ($this->sex) {
            case 'm':
                return 'Sehr geehrter Herr';
            case 'f':
                return 'Sehr geehrte Frau';
            default:
                return 'Hallo';
        }
    }

    /**
     * Resets this user's password to a random new one and
     * send the new password to the user's e-amil address.
     */
    public function resetPassword()
    {
        $password = $this->_generatePassword();
        $this->password = $password;
        TableRegistry::get('Users')->save($this);
        $this->getMailer('User')->send('resetPassword', [$this, $password]);
        $this->log("Password for UID {$this->id} was reset.", LogLevel::INFO);
    }

    protected function _generatePassword()
    {
        if (static::$_pwgenInstance === null) {
            $config = Configure::read('Misc.passwords') + [
                    'length' => 8,
                    'secure' => false,
                    'numerals' => true,
                    'capitalize' => true,
                    'ambiguous' => true,
                    'no-vowels' => false,
                    'symbols' => false
                ];

            static::$_pwgenInstance = new PWGen($config['length'], $config['secure'], $config['numerals'],
                $config['capitalize'],
                $config['ambiguous'], $config['no-vowels'], $config['symbols']);
        }
        return static::$_pwgenInstance->generate();
    }

    /**
     * Check if this is the correct password for this user.
     *
     * Note: This is NOT used by FormAuthenticate for the login,
     * this is just here for internal purposes.
     *
     * @param $password string The Password to check.
     * @return bool True if the password is correct, false otherwise.
     */
    public function checkPassword($password)
    {
        $hasher = $this->_getPasswordHasher();
        return $hasher->check($password, $this->password); //$this->password is the hash, obviously
    }

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array
     */
    protected $_hidden = [
        'password'
    ];
}
