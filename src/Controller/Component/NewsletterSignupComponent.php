<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Http\Client;
use Cake\Core\Configure;

/**
 * Component for signing people up to the newsletter. This
 * will just "enter their data" on the HTTP page. A confirmation
 * link is then send to them. This just simulates normal signup
 * procedure.
 */
class NewsletterSignupComponent extends Component
{
    public function signup($email)
    {
		$url = $this->getSignupLink();
		if (!$url) {
			return false;
		}
		
        $http = new Client();
		$resp = $http->post($url, [
			'email' => $email,
			'emailconfirm' => $email,
			'htmlemail'	=> 1,
			'list[2]' => 'signup',
			'listname[2]' => 'nachrichtenbrief',
			'subscribe' => 'Für den Nachrichtenbrief anmelden!'
		]);

		return $resp->isOk();
    }
	
	public function getSignupLink()
	{
		$url = Configure::read('App.newsletterUrl');
		if (!$url) {
			return false;
		}
		return $url . '?p=subscribe';
	}
	
	public function getUnsubscribeLink()
	{
		$url = Configure::read('App.newsletterUrl');
		if (!$url) {
			return false;
		}
		return $url . '?p=unsubscribe';
	}
}