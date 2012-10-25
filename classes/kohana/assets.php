<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Created by JetBrains PhpStorm.
 * User: sfagnum
 * Date: 08.06.12
 * Time: 18:23
 * To change this template use File | Settings | File Templates.
 */

abstract class Kohana_Assets
{
    const SCRIPTS = 1;
    const STYLES = 2;
    const INLINEJS = 3;
    const INLINECSS = 3;
    const ALL = 5;
    /**
     * @var
     */
    private static $_instance;
    /**
     * @var
     */
    private $_namespacesList;
    /**
     * @var
     */
    public static $namespace;
    /**
     * @var
     */
    public static $scripts;
    /**
     * @var
     */
    public static $styles;

    /**
     * @var
     */
    private static $priority;

    /**
     * @static
     *
     * @param null $namespace
     *
     * @return Assets
     */
    public static function instance($namespace = NULL)
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new Assets();
        }
        return self::$_instance->_setNamespace($namespace);
    }

    /**
     * @param null $namespace
     *
     * @return Assets
     */
    private function _setNamespace($namespace)
    {
        $this->_namespacesList = isset($this->_namespacesList) ? $this->_namespacesList : array();

        if (!empty($namespace) && !is_null($namespace)) {
            if (array_key_exists($namespace, $this->_namespacesList)) {
                self::$scripts = $this->_namespacesList[$namespace]['scripts'];
                self::$styles = $this->_namespacesList[$namespace]['styles'];
            } else {
                $this->_namespacesList[$namespace] = array('styles' => array(),
                                                           'scripts'=> array());
                self::$priority[$namespace] = array('styles' => 0,
                                                    'scripts'=> 0);
                self::$scripts = array();
                self::$styles = array();
            }
            self::$namespace = $namespace;
        } else {
            self::$namespace = 'defaultNamespace';
        }
        return $this;
    }

    /**
     * Bind scripts to current namespace
     *
     * @param        $scripts
     * @param string $directory
     *
     * @return Assets
     */
    public function addScripts($scripts, $directory = '', $priority = 0)
    {
        if (empty($scripts)) {
            return $this;
        }
        $this->_addAssetsToCurrentNamespace('scripts', $scripts, $directory, $priority);
        return $this;
    }

    /**
     * @param        $styles
     * @param string $directory
     *
     * @return Assets
     */
    public function addStyles($styles, $directory = '', $priority = 0)
    {
        if (empty($styles)) {
            return $this;
        }
        $this->_addAssetsToCurrentNamespace('styles', $styles, $directory, $priority);
        return $this;
    }

    /**
     * @param             $type; scripts or styles
     * @param  $assets
     * @param string      $directory
     * @param int         $priority
     *
     * @return mixed
     */
    private function _addAssetsToCurrentNamespace($type, $assets, $directory, $priority)
    {
        if (!$type) {
            return;
        }
        $directory = (!empty($directory) && !is_array($directory)) ? $directory : '';
        if (is_array($assets)) {
            foreach ($assets as $asset) {
                if (!empty($asset) && !is_array($asset)) {
                    $this->_namespacesList[self::$namespace][$type][$directory . $asset] =
                        self::_getPriority($priority, $type);
                } elseif (array_key_exists('priority', $asset)) {
                    $this->_namespacesList[self::$namespace][$type][$directory . $asset] = $asset['priority'];
                }
            }
        } else {
            if (!empty($assets) && !is_array($assets)) {
                $this->_namespacesList[self::$namespace][$type][$directory . $assets] =
                    self::_getPriority($priority, $type);
            }
        }
        asort($this->_namespacesList[self::$namespace][$type]);
    }

    /**
     * @static
     *
     * @param $priority array
     * @param $type     string
     *
     * @return Array
     */
    private static function _getPriority($priority, $type)
    {
        return ($priority !== 0 ? $priority : ++self::$priority[self::$namespace][$type]);
    }

    /**
     * @param $code
     *
     * @return Kohana_Assets
     */
    public function addInlineJS($code)
    {
        if (empty($code)) {
            return;
        }
        $wrap = '<script type="text/javascript">' . $code . '</script>';
        $this->_namespacesList[self::$namespace]['inlineJS'][] = $wrap;
        return $this;
    }

    /**
     * @param $css
     *
     * @return Kohana_Assets
     */
    public function addInlineCSS($css)
    {
        if (empty($css)) {
            return;
        }
        $wrap = '<style>' . $css . '</style>';
        $this->_namespacesList[self::$namespace]['inlineCSS'][] = $wrap;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStyles()
    {
        return $this->_namespacesList[self::$namespace]['styles'];
    }

    /**
     * @return Array
     */
    public function getScripts()
    {
        return $this->_namespacesList[self::$namespace]['scripts'];
    }



    /**
     * Render assets
     * @param integer $assetsType Assets::ALL, Assets::STYLES
     */
    public function render($assetsType = NULL)
    {
        $assetsType = is_null($assetsType) ? Assets::ALL : $assetsType;
        switch ($assetsType) {
            case Assets::SCRIPTS:
                $this->_renderScripts();
                break;
            case Assets::STYLES:
                $this->_renderStyles();
                break;
            case Assets::INLINEJS:
                $this->_renderInlineJS();
                break;
            case Assets::INLINECSS:
                $this->_renderInlineCSS();
                break;
            default:
                $this->_renderStyles();
                $this->_renderScripts();
                $this->_renderInlineJS();
                $this->_renderInlineCSS();
                break;
        }
    }

    private function _renderScripts()
    {
        foreach ($this->getScripts() as $script=> $priority) {
            if (!empty($script)) {
                echo HTML::script($script) . "\n";
            }
        }
    }

    private function _renderStyles()
    {
        foreach ($this->getStyles() as $style=> $priority) {
            if (!empty($style)) {
                echo HTML::style($style) . "\n";
            }
        }
    }

    private function _renderInlineJS()
    {
        if (isset($this->_namespacesList[self::$namespace]['inlineJS'])) {
            foreach ($this->_namespacesList[self::$namespace]['inlineJS'] as $inlineScript) {
                echo ($inlineScript) . "\n";
            }
        }
    }

    private function _renderInlineCSS()
    {
        if (isset($this->_namespacesList[self::$namespace]['inlineCSS'])) {
            foreach ($this->_namespacesList[self::$namespace]['inlineCSS'] as $inlineStyle) {
                echo ($inlineStyle) . "\n";
            }
        }
    }

    function __toString()
    {
        $this->render();
    }
}
