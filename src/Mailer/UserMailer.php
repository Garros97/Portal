<?php
namespace App\Mailer;

use App\Model\Entity\Course;
use App\Model\Entity\Registration;
use App\Model\Entity\User;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\TableRegistry;
use CakePdf\Pdf\CakePdf;
use Psr\Log\LogLevel;
use Cake\Filesystem\File;

/**
 * UserMailer mailer.
 */
class UserMailer extends AppMailer
{
    public function implementedEvents()
    {
        return [
            'Model.User.afterRegister' => 'onRegistration',
            'Model.User.afterRegisterForProject' => 'onRegisterForProject',
            'Model.User.movedUp' => 'onMovedUp'
        ];
    }

    //event handlers
    public function onRegistration(Event $event, EntityInterface $entity, $source, $password = null)
    {
        $this->send('accountCreated', [$entity, $source, $password]);
    }

    public function onRegisterForProject(Event $event, $user, $registration)
    {
        $this->send('registrationComplete', [$user, $registration]);
    }

    public function onMovedUp(Event $event, $cid, $user)
    {
        $this->send('movedUp', [TableRegistry::get('Courses')->get($cid, ['contain' => 'Projects']), $user]);
    }

    //mails
    /**
     * @param $user \App\Model\Entity\User
     * @param $registration \App\Model\Entity\Registration
     * @return $this
     */
    public function registrationComplete($user, $registration)
    {
        $cakePdf = new CakePdf();
        $cakePdf
            ->templatePath('Registrations/pdf')
            ->template("confirmations/{$registration->project->confirmation_mail_template}", 'confirmation')
            ->viewVars(compact('registration'));
        $pdfData = $cakePdf->output();
        if ($registration->project->hasTag('confirmationAppendix')) {
            $appendixFile = ROOT . DS . Configure::read('App.uploads') . DS . 'confirmation_appendices' . DS . $registration->project->getTagValue('confirmationAppendix');
            $pdfData = $this->mergeConfirmationPDF($pdfData, $appendixFile);
        }

        return $this
            ->to($user->email)
            ->setSubject("Ihre Anmeldebestätigung - {$registration->project->name}")
            ->set(compact('user', 'registration'))
            ->setAttachments([
                "anmeldebestätigung_{$registration->id}.pdf" => [
                    'data' => $pdfData,
                    'mimetype' => 'application/pdf'
                ]
            ]);
    }

    /**
     * @param $user \App\Model\Entity\User
     * @param $newPassword string The new password (in clear text)
     * @return $this
     */
    public function resetPassword($user, $newPassword)
    {
        return $this
            ->to($user->email)
            ->setSubject('Ihr neues Passwort')
            ->set(compact('user', 'newPassword'));
    }

    /**
     * @param $user \App\Model\Entity\User
     * @param $source string "admin" or "self" depending on registration source
     * @param $password string When source=="admin", this is the new password of the user
     * @return $this
     */
    public function accountCreated($user, $source, $password)
    {
        return $this
            ->to($user->email)
            ->setSubject('Account erstellt')
            ->set(compact('user', 'source', 'password'));
    }

    /**
     * @param $course Course
     * @param $user User
     * @return $this
     */
    public function movedUp($course, $user)
    {
        return $this
            ->to($user->email)
            ->setSubject("Von der Warteliste aufgerückt: {$course->name}")
            ->set(compact('user', 'course'));
    }

    /**
     * @param $registration Registration
     * @param $mailTemplate string
     * @param $fieldName string
     * @param $newFieldValue string
     * @return this
     */
    public function notifyCustomFieldChanged($registration, $mailTemplate, $fieldName, $newFieldValue)
    {
        return $this
            ->to($registration->user->email)
            ->setSubject("Benachrichtigung {$registration->project->name}")
            ->set(compact('fieldName', 'newFieldValue') + ['project' => $registration->project, 'user' => $registration->user])
            ->setTemplate('customFieldChanged/' . $mailTemplate);
    }

    /**
     * Merge pdf files using ghostscript. Will always use the same temporary files to not clutter the file system.
     *
     * @param $confirmation string
     * @param $appendixPath string
     * @return false|string
     */
    public function mergeConfirmationPDF($confirmation, $appendixPath)
    { //TODO: add phpdoc
        $tmpPath = TMP . DS . "tmp_in.pdf";
        $outputPath = TMP . DS . "tmp_out.pdf";
        $confirmationFile = new File($tmpPath, true, 0777);
        $outputFile = new File($outputPath);
        $confirmationFile->write($confirmation);

        shell_exec("gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=$outputPath $tmpPath $appendixPath");
        return $outputFile->read();
    }
}
