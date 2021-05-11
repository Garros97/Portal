<?php

namespace App\Controller\Component;

use Cake\Controller\Component\AuthComponent;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

/**
 * Extended AuthComponent for chell.
 * @package App\Controller\Component
 * @property \Cake\Controller\Component\RequestHandlerComponent $RequestHandler
 * @property \Cake\Controller\Component\FlashComponent $Flash
 */
class ChellAuthComponent extends AuthComponent
{
    /**
     * Checks if the current user has the given right.
     *
     * If $right is a callable, the callable will be called will
     * all rights of the user. When at least one invocation returns
     * true, this method will return true. Using this pattern, a
     * custom right check can be implemented.
     *
     * @param $right string|callable Name of the right to check, or callable
     * @return bool True if the user has the right, false otherwise.
     */
    public function userHasRight($right)
    {
        if (is_callable($right)) {
            return collection($this->user('rights'))->some($right);
        }
        return in_array($right, $this->user('rights'));
    }

    public function userHasTag($tagName)
    {
        $tags = $this->user('tags');
        if ($tags === null)
            return false;
        return array_key_exists($tagName, $tags);
    }

    public function userGetTagValue($tagName, $defaultValue = null)
    {
        $tags = $this->user('tags');
        if ($tags === null || !array_key_exists($tagName, $tags)) {
            return $defaultValue;
        }
        return $tags[$tagName];
    }

    /**
     * This method will return all subresource IDs a user might access for the
     * given right. If the user has the general form of the right, true will
     * be returned.
     *
     * @param $rightBaseName string The name of the right, without subresources.
     * @return array|true An array of accessible subresource IDs, or null.
     */
    public function userGetAccessibleSubresourceIds($rightBaseName)
    {
        if ($this->userHasRight($rightBaseName)) {
            return true;
        }
        return collection($this->user('rights'))->filter(function ($x) use ($rightBaseName) {
            return strpos($x, $rightBaseName . '/') === 0;
        })->map(function ($x) {
            return (int)explode('/', $x, 2)[1];
        })->toArray();
    }

    /**
     * Reloads the current user from the DB to reflect changes to rights and
     * tags.
     *
     * This method only works in debug mode because the method used is somewhat
     * hacked together (depends on guesses). Logging in again is safer.
     */
    public function reloadCurrentUser()
    {
        if (!Configure::read('debug')) {
            throw new \LogicException('Reloading this current user is only allowed in debug mode.');
        }

        //This reflects what the login process currently (!) does:
        $user = TableRegistry::get('Users')->find('auth')->where(['id' => $this->user('id')])->firstOrFail();
        $user->unsetProperty('password');
        $this->setUser($user);
    }
}