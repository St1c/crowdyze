<?php

namespace Test;

class Presenter extends \Nette\Object {

        private $container;
        private $presenter;
        private $presName;

        public function __construct(\Nette\DI\Container $container) {
                $this->container = $container;
        }

        /**
         * @param $presName string Fully qualified presenter name.
         */
        public function init($presName) {
                $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
                $this->presenter = $presenterFactory->createPresenter($presName);

                $this->presenter->autoCanonicalize = FALSE;
                $this->presName = $presName;
        }

        public function test($action, $method = 'GET', $params = array(), $post = array()) {
                $params['action'] = $action;
                $request = new \Nette\Application\Request($this->presName, $method, $params, $post);
                $response = $this->presenter->run($request);
                return $response;
        }

        public function testAction($action, $method = 'GET', $params = array(), $post = array()) {
                $response = $this->test($action, $method, $params, $post);

                \Tester\Assert::true($response instanceof \Nette\Application\Responses\TextResponse);
                \Tester\Assert::true($response->getSource() instanceof \Nette\Templating\ITemplate);

                $html = (string)$response->getSource();
                $dom = \Tester\DomQuery::fromHtml($html);
                \Tester\Assert::true($dom->has('title'));

                return $response;
        }

        public function testForm($action, $method = 'POST', $post = array()) {
                $response = $this->test($action, $method, $post);

                \Tester\Assert::true($response instanceof \Nette\Application\Responses\RedirectResponse);

                return $response;
        }

}