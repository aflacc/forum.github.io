<?php

/*
  +------------------------------------------------------------------------+
  | Phosphorum                                                             |
  +------------------------------------------------------------------------+
  | Copyright (c) 2013-present Phalcon Team (https://www.phalconphp.com)   |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file LICENSE.txt.                             |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Sergii Svyrydenko <sergey.v.sviridenko@gmail.com>             |
  +------------------------------------------------------------------------+
*/

namespace Phosphorum\Services\Controller;

use Phalcon\Di;
use Phalcon\Registry;
use Phalcon\Assets\Manager;
use Phosphorum\Assets\Filters\NoneFilter;
use Phosphorum\Exception\RuntimeException;

/**
 * Phosphorum\Services\Controller\AssetsService
 *
 * @package Phosphorum\Services\Controller
 */

class AssetsService
{
    /** @var Di $di */
    private $di;

    /** @var Registry $registry */
    private $registry;

    /** @var Manager $manager */
    private $manager;

    public function __construct(Manager $manager = null)
    {
        $this->di = Di::getDefault();
        $this->registry = $this->di->get('registry');

        if ($manager === null) {
            $manager = $this->di->get('assets');
        }
        $this->manager = $manager;
    }

    /**
     * @return Manager
     */
    public function getManager()
    {
        $this->setJsCollection();
        $this->setCssCollection();

        return $this->manager;
    }

    /**
     * @return void
     * @throws RuntimeException
     */
    private function setJsCollection()
    {
        try {
            $this->manager
                ->collection('globalJs')
                ->setTargetPath($this->getPath('public') . 'assets/global.js')
                ->setTargetUri('assets/global.js')
                ->addJs($this->getPath('public') . 'js/jquery-3.2.1.min.js', true, false)
                ->addJs($this->getPath('public') . 'js/bootstrap.min.js', true, false)
                ->addJs($this->getPath('public') . 'js/editor.min.js', true, false)
                ->addJs($this->getPath('public') . 'js/forum.js', true)
                ->addJs($this->getPath('public') . 'js/prism.js', true)
                ->join(true)
                ->addFilter(new NoneFilter());
        } catch (RuntimeException $e) {
            $this->di->get('logger')->error($e->getMessage());
        }
    }

    /**
     * @return void
     * @throws RuntimeException
     */
    private function setCssCollection()
    {
        $params = $this->getCssCollectionParam();

        try {
            $this->manager
                ->collection($params['collectionName'])
                ->setTargetPath($this->getPath('public') . "assets/{$params['fileName']}")
                ->setTargetUri("assets/{$params['fileName']}")
                ->addCss($this->getPath('public') . 'css/bootstrap.min.css', true, false)
                ->addCss($this->getPath('public') . 'css/editor.css', true)
                ->addCss($this->getPath('public') . 'css/fonts.css', true)
                ->addCss($this->getPath('public') . 'css/octicons.css', true)
                ->addCss($this->getPath('public') . 'css/diff.css', true)
                ->addCss($this->getPath('public') . 'css/style.css', true)
                ->addCss($this->getPath('public') . 'css/prism.css', true)
                ->addCss($this->getPath('public') . "css/{$params['themeFile']}", true)
                ->join(true)
                ->addFilter(new NoneFilter());
        } catch (RuntimeException $e) {
            $this->di->get('logger')->error($e->getMessage());
        }
    }

    /**
     * @return array
     */
    private function getCssCollectionParam()
    {
        $param['collectionName'] = 'globalCss';
        $param['fileName'] = 'global-default.css';
        $param['themeFile'] = 'theme.css';

        if ($this->di->has('session') && $this->di->get('session')->get('identity-theme') === 'L') {
            $param['collectionName'] = 'globalWhiteCss';
            $param['fileName'] = 'global-white.css';
            $param['themeFile'] = 'theme-white.css';
        }

        return $param;
    }

    /**
     * Get path from registry
     * @param string $directory
     * @return string
     */
    protected function getPath($directory)
    {
        return $this->registry->offsetGet('paths')->{$directory};
    }
}