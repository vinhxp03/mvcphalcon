<?php

error_reporting(E_ALL);

use Phalcon\Loader;
use Phalcon\Mvc\Router;
use Phalcon\DI\FactoryDefault;
use Phalcon\Mvc\Application as BaseApplication;
use Phalcon\Mvc\Url;

class Application extends BaseApplication
{
    /**
     * Register the services here to make them general or register in the ModuleDefinition to make them module-specific
     */
    protected function registerServices()
    {

        $di = new FactoryDefault();

        $loader = new Loader();

        $di->set(
            'url',
            function () {
                $url = new Url();

                $url->setBaseUri('http://localhost:81/mvc/multiple/');

                return $url;
            }
        );
        /**
         * We're a registering a set of directories taken from the configuration file
         */
        $loader
            ->registerDirs([__DIR__ . '/../apps/library/'])
            ->register();

        // Registering a router
        $di->set('router', function () {

            $router = new Router();

            $router->setDefaultModule("frontend");

            $router->add('/:controller/:action', [
                'module'     => 'frontend',
                'controller' => 1,
                'action'     => 2,
            ])->setName('frontend');

            $router->add("/login", [
                'module'     => 'backend',
                'controller' => 'login',
                'action'     => 'index',
            ])->setName('backend-login');

            $router->add("/admin/products/:action", [
                'module'     => 'backend',
                'controller' => 'products',
                'action'     => 1,
            ])->setName('backend-product');

            $router->add("/products/:action", [
                'module'     => 'frontend',
                'controller' => 'products',
                'action'     => 1,
            ])->setName('frontend-product');

            $router->add("/student", [
                'module'     => 'backend',
                'controller' => 'student',
                'action'     => 'index',
            ])->setName('backend-student');

            return $router;
        });

        $this->setDI($di);
    }

    public function main()
    {

        $this->registerServices();

        // Register the installed modules
        $this->registerModules([
            'frontend' => [
                'className' => 'Multiple\Frontend\Module',
                'path'      => '../apps/frontend/Module.php'
            ],
            'backend'  => [
                'className' => 'Multiple\Backend\Module',
                'path'      => '../apps/backend/Module.php'
            ]
        ]);

        echo $this->handle()->getContent();
    }
}

$application = new Application();
$application->main();
