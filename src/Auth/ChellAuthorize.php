<?php
namespace App\Auth;

use Cake\Auth\BaseAuthorize;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Http\ServerRequest;
use Cake\Log\LogTrait;
use Cake\ORM\TableRegistry;
use Psr\Log\LogLevel;

class ChellAuthorize extends BaseAuthorize
{
    use LogTrait;

    /**
     * Controller for the request.
     *
     * @var \App\Controller\AppController
     */
    protected $_controller = null;

    /**
     * {@inheritDoc}
     */
    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        parent::__construct($registry, $config);
        $this->controller($registry->getController());
    }

    /**
     * Get/set the controller this authorize object will be working with. Also
     * checks that the $rights property is present.
     *
     * @param Controller|null $controller null to get, a controller to set.
     * @return \Cake\Controller\Controller
     * @throws \Cake\Core\Exception\Exception If controller does not have a $rights array.
     */
    public function controller(Controller $controller = null)
    {
        if ($controller) {
            if (!property_exists($controller, 'rights')) {
                throw new Exception(sprintf(
                    '%s does not have $rights property.',
                    get_class($controller)
                ));
            }
            if (!is_array($controller->rights)) {
                throw new Exception(sprintf(
                    '$rights of controller %s is not an array.',
                    get_class($controller)
                ));
            }
            $this->_controller = $controller;
        }
        return $this->_controller;
    }


    /**
     * Checks user authorization.
     *
     * @param array $user Active user data
     * @param \Cake\Http\ServerRequest $request Request instance.
     * @return bool
     * @throws \Cake\Core\Exception\Exception If the action is not in the $rights array of the current controller
     *  or the content is not an array or string.
     */
    public function authorize($user, ServerRequest $request)
    {
        $rights = $this->_controller->rights;

        if (!array_key_exists($request->getParam('action'), $rights))
            throw new Exception(sprintf('No entry in the $rights array for the action %s of controller %s',
                $request->getParam('action'), get_class($this->_controller)));

        $requiredRights = $rights[$request->getParam('action')];

        if (is_string($requiredRights))
            $requiredRights = [$requiredRights];
        if (!is_array($requiredRights))
            throw new Exception(sprintf('The $rigths table entry is neither string nor array for action %s of controller %s',
                $request->getParam('action'), get_class($this->_controller)));

        if ($request->getParam('prefix') === 'admin')
            $requiredRights[] = 'ADMIN'; //all /admin pages require the ADMIN right

        foreach($requiredRights as $right) {
            if (!$this->_checkRight($right, $request)) {
                $this->log(sprintf('Denied: User %s(%d) tried to access %s but did not have the %s right.',
                    $user['username'], $user['id'], $request->url, $right), LogLevel::NOTICE);
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if the current user has the specified right.
     *
     * If necessary a call to the controller is made to find the exact
     * subresources required.
     *
     * @param $right string The requested right
     * @param $request \Cake\Http\ServerRequest The request
     * @return bool True if the user has the right, false otherwise.
     */
    protected function _checkRight($right, $request)
    {
        if (strpos($right, '/') === false) { //simple right
            return $this->_controller->Auth->userHasRight($right);
        }

        list($right, $subresource) = explode('/', $right, 2);

        if ($this->_controller->Auth->userHasRight($right)) { //general form
            return true;
        }

        if ($subresource === '?') { //call controller
            $subresource = $this->_controller->getRequiredSubresourceIds($right, $request);
        }

        if ($subresource === false) { //controller might have returned this
            return false;
        } elseif (is_array($subresource)) { //array -> every subresource is required
            return count($subresource) == 0 || collection($subresource)->every(function ($x) use ($right, $request) {
                return $this->_checkRight($right . '/' . $x, $request); //recursive call
            }); //call to count is necessary because of Cake's arcane view to set theory :(
        } elseif (ctype_digit((string)$subresource)) { //number -> that very subresource is required
            return $this->_controller->Auth->userHasRight($right . '/' . $subresource);
        } elseif (preg_match('/^\\$(\\d+)$/', $subresource, $matches)) { //form "RIGHT/$x"
            $subresource = $request->getParam('pass')[(int)$matches[1]];
            return $this->_controller->Auth->userHasRight($right . '/' . $subresource);
        } elseif ($subresource === 'any') {
            return $this->_controller->Auth->userHasRight(function ($rightName) use ($right) {
                return $rightName === $right || strpos($rightName, $right . '/') === 0;
            });
        } else {
            throw new \LogicException("Unrecognised subresource format $subresource");
        }
    }
}