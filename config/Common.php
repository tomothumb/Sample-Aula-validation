<?php
namespace Aura\Framework_Project\_Config;

use Aura\Di\Config;
use Aura\Di\Container;

use Aura\Filter\FilterFactory;
use FOA\Filter_Input_Bundle\Filter;


class Common extends Config
{
    public function define(Container $di)
    {
        $di->set('aura/project-kernel:logger', $di->lazyNew('Monolog\Logger'));

    }

    public function modify(Container $di)
    {
        $this->modifyLogger($di);
        $this->modifyCliDispatcher($di);
        $this->modifyWebRouter($di);
        $this->modifyWebDispatcher($di);
    }

    protected function modifyLogger(Container $di)
    {
        $project = $di->get('project');
        $mode = $project->getMode();
        $file = $project->getPath("tmp/log/{$mode}.log");

        $logger = $di->get('aura/project-kernel:logger');
        $logger->pushHandler($di->newInstance(
            'Monolog\Handler\StreamHandler',
            array(
                'stream' => $file,
            )
        ));
    }

    protected function modifyCliDispatcher(Container $di)
    {
        $context = $di->get('aura/cli-kernel:context');
        $stdio = $di->get('aura/cli-kernel:stdio');
        $logger = $di->get('aura/project-kernel:logger');
        $dispatcher = $di->get('aura/cli-kernel:dispatcher');
        $dispatcher->setObject(
            'hello',
            function ($name = 'World') use ($context, $stdio, $logger) {
                $stdio->outln("Hello {$name}!");
                $logger->debug("Said hello to '{$name}'");
            }
        );
    }

    public function modifyWebRouter(Container $di)
    {
        $router = $di->get('aura/web-kernel:router');

        $router->add('hello', '/')
            ->setValues(array('action' => 'hello'));

        $router->add('send', '/send')
            ->setValues(array('action' => 'send'));
    }

    public function modifyWebDispatcher($di)
    {

        $dispatcher = $di->get('aura/web-kernel:dispatcher');

        $dispatcher->setObject('hello', function () use ($di) {
            $response = $di->get('aura/web-kernel:response');
            $response->content->set('Hello World!');
        });

        $dispatcher->setObject('send', function () use ($di) {

            $request = $di->get('aura/web-kernel:request');
            $input = $request->post->get();

            $response = $di->get('aura/web-kernel:response');
            $response->content->setType('application/json');
            $response_content = array();



            $filter_factory = new FilterFactory();
            $filter = $filter_factory->newInstance();
            $filter->addSoftRule('first_name', $filter::IS, 'string');
            $filter->addSoftRule('first_name', $filter::IS, 'strlenBetween', 6, 12);
            $success = $filter->values($input);
            if ( $success) {
                $response_content["message"] = "User input is valid.";
            }else{
                $response_content["message"][] = "User input is not valid.";
                $messages = $filter->getMessages();
                $response_content["message"] = $messages;
            }

            $response_content["data"] = $input;
            $response->content->set($response_content);
            $json = json_encode($response->content->get());
            $response->content->set($json);


        });
    }
}
