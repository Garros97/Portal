<?php
namespace App\Controller\Admin;

use Cake\Event\Event;

/**
 * The PagesController for the admin interface. This is exactly the same as
 * the normal pages controller, but uses takes it views from /Admin/Page.
 * @package App\Controller\Admin
 */
class PagesController extends \App\Controller\PagesController
{
    public function beforeFilter(Event $event)
    {
        parent::beforeRender($event);
        $this->Auth->deny('display'); //the admin pages are not public, so re-deny it.
    }
}