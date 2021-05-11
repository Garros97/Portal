<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use App\Controller\Component\ChellAuthComponent;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Database\Type;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Mailer\MailerAwareTrait;
use Cake\Utility\Hash;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link http://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 * @property \App\Controller\Component\ChellAuthComponent $Auth
 */
class AppController extends Controller
{
    use MailerAwareTrait;

    /**
     * Rights for the specific action. Use the format
     *
     * 'action' => ['RIGHT1', 'RIGHT2']
     *
     * *all* specified rights will be required.
     *
     * There is one exception: All /admin pages will required additionally the
     * 'ADMIN' right.
     *
     * @var array
     */
    public $rights = [];

    /**
     * Returns the required subresource id(s) for the given request.
     *
     * This function can return either a ID, an array of IDs (in this case
     * all IDs are required), "any" (access to any subresource
     * is sufficient) or false, to deny the requst.
     *
     * @param $request \Cake\Http\ServerRequest The current request
     * @param $right string The current right
     * @return string|array|false The required id(s), "any" or false.
     */
    public function getRequiredSubresourceIds($right, $request)
    {
        throw new \LogicException('Override getRequiredSubresourceIds method on your controller');
    }

    public $helpers = [
        'Html' => [
            'className' => 'Bootstrap.Html'
        ],
        'Form' => [
            'className' => 'Bootstrap.Form',
            'widgets' => [
                'datePicker' => ['DatePicker', '_view'],
                'static' => ['StaticControl'],
            ],
            'templates' => [
                'staticControl' => '<p class="form-control-static">{{value}}</p>',
            ],
            'columns' => [
                'md' => [
                    'label' => 2,
                    'input' => 6,
                    'error' => 4
                ],
                'sm' => [
                    'label' => 2,
                    'input' => 10,
                    'error' => 0
                ]
            ]
        ],
        'Paginator' => [
            'className' => 'Bootstrap.Paginator',
            'templates' => [
                'sortAsc' => '<a class="asc" href="{{url}}">{{text}}&nbsp;<i class="glyphicon glyphicon-menu-down" aria-hidden="true"></i></a>',
                'sortDesc' => '<a class="desc" href="{{url}}">{{text}}&nbsp;<i class="glyphicon glyphicon-menu-up" aria-hidden="true"></i></a>',
            ]
        ],
        'Modal' => [
            'className' => 'Bootstrap.Modal'
        ],
        'Bootstrap.Navbar',
        'Panel' => [
            'className' => 'Bootstrap.Panel'
        ]
    ];


    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('Flash');
        $this->loadComponent('Auth', [
            'loginAction' => [
                'controller' => 'Users',
                'action' => 'login',
                'prefix' => false
            ],
            'logoutAction' => [
                'controller' => 'Users',
                'action' => 'logout',
                'prefix' => false
            ],
            'authorize' => ['Chell'], //use our custom authorizer
            'authError' => 'Sie haben keine Berechtigung diese Seite aufzurufen.',
            'authenticate' => [
                'Form' => [
                    'finder' => 'auth'
                ]
            ],
            'className' => 'ChellAuth'
        ]);

        /*
         * Enable the following components for recommended CakePHP security settings.
         * see http://book.cakephp.org/3.0/en/controllers/components/security.html
         */
        //$this->loadComponent('Security');
        //$this->loadComponent('Csrf');

        //wire-up events
        $this->loadModel('Users')->getEventManager()->on($this->getMailer('User'));
        $this->Auth->getEventManager()->on('Auth.afterIdentify', function () {
            //make sure these are cleared on each login
            $this->request->getSession()->delete('login_as');
            $this->request->getSession()->delete('activeRegistration');
        });
        EventManager::instance()->on('Mail.afterSend', function ($event, $result) {
            if (Hash::get($this->request->getAttribute('params'), 'prefix',
                    '') == 'admin'
            ) { //only notify if in admin area
                preg_match('/^To: (.*)$/m', $result['headers'], $matches); //HACK to extract the mail address...
                $this->Flash->success("Eine E-Mail an <b>{$matches[1]}</b> wurde versendet.", ['escape' => false]);
            }
        });
        EventManager::instance()->on('Mail.failed', function ($event, $ex) {
            $this->Flash->error('Das Versenden einer E-Mail ist fehlgeschlagen. Bitte wenden Sie sich an einen Administrator.');
        });

        if (Configure::read('debug') && $this->Auth->userHasTag('debugValidation')) {
            $this->getEventManager()->on('Controller.beforeRender', function ($event) {
                /** @var AppController $controller */
                $controller = $event->getSubject();
                foreach ($controller->viewVars as $viewVar) {
                    if ($viewVar instanceof EntityInterface && $viewVar->getErrors()) {
                        debug($viewVar->getErrors());
                    }
                }
            });
        }
    }

    /**
     * Before render callback.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     * @return \Cake\Http\Response|null|void
     */
    public function beforeRender(Event $event)
    {
        $this->viewBuilder()->setTheme('Bootstrap');

        if (isset($this->Auth) && $this->Auth !== false) { //AuthController might not be loaded when we are rendering an Exception
            $this->set('loginUrl', $this->Auth->getConfig('loginAction'));
            $this->set('logoutUrl', $this->Auth->getConfig('logoutAction'));
        }

        if (!array_key_exists('_serialize', $this->viewVars) &&
            in_array($this->response->getType(), ['application/json', 'application/xml'])
        ) {
            $this->set('_serialize', true); //automatically set _serialize => true if not specified
        }
    }
}
