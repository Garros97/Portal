<?php
namespace App\Mailer;

use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Log\LogTrait;
use Cake\Mailer\Mailer;
use Psr\Log\LogLevel;

class AppMailer extends Mailer
{
    use LogTrait;

    /** {@inheritdoc} */
    public function send($action, $args = [], $headers = [])
    {
        try {
            $result =  parent::send($action, $args, $headers);
            EventManager::instance()->dispatch(new Event('Mail.afterSend', $this, [$result]));
            return $result;
        } catch (\Exception $e) { //avoid that problems with mails crash the application
            $to = implode(';', $this->_email->getTo());
            $this->log("Error while sending out E-Mail to $to for $action, ex. {$e->getMessage()}", LogLevel::ERROR, [
                'email' => $this->_email,
                'exception' => $e,
            ]);
            EventManager::instance()->dispatch(new Event('Mail.failed', $this, [$e]));
            return false;
        }
    }
}