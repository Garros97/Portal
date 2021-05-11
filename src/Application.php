<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.3.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App;

use Cake\Core\Configure;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\BaseApplication;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 */
class Application extends BaseApplication
{
    public function bootstrap()
    {
        parent::bootstrap();

        if (extension_loaded('runkit') && Configure::read('debug') && Configure::read('Misc.removeDebugInfo')) {
            //if enabled, replace the __debugInfo method from all Entity classes with a more useful version.
            $entityFolder = new \Cake\Filesystem\Folder(\Cake\Core\App::path('Model/Entity')[0]);
            foreach ($entityFolder->find() as $file) {
                $className = '\\App\\Model\\Entity\\' . substr($file, 0, -4); //remove the ".php"
                if (!is_subclass_of($className, '\\Cake\\Datasource\\EntityInterface')) {
                    continue;
                }
                //runkit_method_remove($className, '__debugInfo');
                runkit_method_redefine($className, '__debugInfo', '', 'return $this->_properties + get_object_vars($this);', RUNKIT_ACC_PUBLIC);
            }
            $extraClasses = []; //add classes (with namespace!) here to kill their __debugInfo also
            foreach ($extraClasses as $extraClass) {
                new $extraClass(); //trigger the autoloader
                runkit_method_remove($extraClass, '__debugInfo');
            }
        }
        /*
         * Only try to load DebugKit in development mode
         * Debug Kit should not be installed on a production system
         */
        if (Configure::read('debug')) {
            $this->addPlugin(\DebugKit\Plugin::class);
        }
    }

    /**
     * Setup the middleware queue your application will use.
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
     * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
     */
    public function middleware($middlewareQueue)
    {
        $middlewareQueue
            // Catch any exceptions in the lower layers,
            // and make an error page/response
            ->add(ErrorHandlerMiddleware::class)

            // Handle plugin/theme assets like CakePHP normally does.
            ->add(AssetMiddleware::class)

            // Add routing middleware.
            ->add(new RoutingMiddleware($this));

        return $middlewareQueue;
    }
}
