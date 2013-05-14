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
    const SCRIPTS = 'scripts';
    const STYLES = 'styles';
    const INLINEJS = 3;
    const INLINECSS = 4;
    const ALL = 5;
    /**
     * @var
     */
    private static $_instance;
    /**
     * @var
     */
    private $_namespaces_list;
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
        if (! isset(self::$_instance))
        {
            self::$_instance = new Assets();
        }
        return self::$_instance->_set_namespace($namespace);
    }

    /**
     * @param null $namespace
     *
     * @return Assets
     */
    private function _set_namespace($namespace)
    {
        $this->_namespaces_list = isset($this->_namespaces_list) ? $this->_namespaces_list : array();

        if (! empty($namespace))
        {
            if (array_key_exists($namespace, $this->_namespaces_list))
            {
                self::$scripts = $this->_namespaces_list[$namespace][self::SCRIPTS];
                self::$styles = $this->_namespaces_list[$namespace][self::STYLES];
            }
            else
            {
                $this->_namespaces_list[$namespace] = array(
                    self::STYLES  => array(),
                    self::SCRIPTS => array()
                );
                self::$priority[$namespace] = array(
                    self::STYLES  => 0,
                    self::SCRIPTS => 0
                );
                self::$scripts = array();
                self::$styles = array();
            }
            self::$namespace = $namespace;
        }
        else
        {
            self::$namespace = 'defaultNamespace';
        }
        return $this;
    }

    /**
     * Bind scripts to current namespace
     *
     * @param mixed  $scripts  can be array or string
     * @param string $directory
     * @param int    $priority
     *
     * @return Assets
     */
    public function add_scripts($scripts, $directory = '', $priority = 0)
    {
        if (empty($scripts))
        {
            return $this;
        }
        $this->_add_assets_to_namespace(self::SCRIPTS, $scripts, $directory, $priority);
        return $this;
    }

    /**
     * @param mixed  $styles
     * @param string $directory
     * @param int    $priority
     *
     * @return Assets
     */
    public function add_styles($styles, $directory = '', $priority = 0)
    {
        if (empty($styles))
        {
            return $this;
        }
        $this->_add_assets_to_namespace(self::STYLES, $styles, $directory, $priority);
        return $this;
    }

    /**
     * @param             $type; scripts or styles
     * @param             $assets
     * @param string      $directory
     * @param int         $priority
     *
     * @return mixed
     */
    private function _add_assets_to_namespace($type, $assets, $directory, $priority)
    {
        if (! $type)
        {
            return;
        }
        $directory = (! empty($directory) AND ! is_array($directory)) ? $directory : '';
        if (is_array($assets) AND count($assets) > 0)
        {
            foreach ($assets as $asset)
            {
                if (is_array($asset))
                {
                    if (array_key_exists('priority', $asset))
                    {
                        $this->_namespaces_list[self::$namespace][$type][$directory.$asset] = $asset['priority'];
                    }
                    else
                    {
                        $this->_namespaces_list[self::$namespace][$type][$directory.$asset]
                            = self::_get_priority($priority, $type);
                    }

                }
                else {
                    $this->_namespaces_list[self::$namespace][$type][$directory.$asset]
                                                = self::_get_priority($priority, $type);
                }

            }
        }
        else
        {
            if (! empty($assets) && ! is_array($assets))
            {
                $this->_namespaces_list[self::$namespace][$type][$directory.$assets]
                    = self::_get_priority($priority, $type);
            }
        }
        asort($this->_namespaces_list[self::$namespace][$type]);
    }

    /**
     * @static
     *
     * @param $priority array
     * @param $type     string
     *
     * @return Array
     */
    private static function _get_priority($priority, $type)
    {
        return ($priority !== 0 ? $priority : ++self::$priority[self::$namespace][$type]);
    }

    /**
     * @param $code
     *
     * @return Kohana_Assets
     */
    public function add_inlineJS($code)
    {
        if (empty($code))
        {
            return;
        }
        $wrap = '<script type="text/javascript">'.$code.'</script>';
        $this->_namespaces_list[self::$namespace]['inlineJS'][] = $wrap;
        return $this;
    }

    /**
     * @param $css
     *
     * @return Kohana_Assets
     */
    public function add_inlineCSS($css)
    {
        if (empty($css))
        {
            return;
        }
        $wrap = '<style>'.$css.'</style>';
        $this->_namespaces_list[self::$namespace]['inlineCSS'][] = $wrap;
        return $this;
    }

    /**
     * @return mixed
     */
    public function get_styles()
    {
        return $this->_namespaces_list[self::$namespace][self::STYLES];
    }

    /**
     * @return Array
     */
    public function get_scripts()
    {
        return $this->_namespaces_list[self::$namespace][self::SCRIPTS];
    }


    /**
     * Render assets
     *
     * @param integer $assetsType Assets::ALL, Assets::STYLES etc.
     */
    public function render($assetsType = NULL)
    {
        echo $this->compile($assetsType);
    }

    public function compile($assetsType = NULL)
    {
        $assetsType = is_null($assetsType) ? Assets::ALL : $assetsType;
        return $this->_compile_all($assetsType);
    }


    private function _compile_all($assetsType)
    {
        $html = NULL;

        switch ($assetsType)
        {
        case self::SCRIPTS:
            $html .= $this->_render_scripts();
            break;
        case self::STYLES:
            $html .= $this->_render_styles();
            break;
        case self::INLINEJS:
            $html .= $this->_render_inlineJS();
            break;
        case self::INLINECSS:
            $html .= $this->_render_inlineCSS();
            break;
        default:
            $html .= $this->_render_styles();
            $html .= $this->_render_scripts();
            $html .= $this->_render_inlineJS();
            $html .= $this->_render_inlineCSS();
            break;
        }
        return $html;
    }

    private function _render_scripts()
    {
        $html = '';
        foreach ($this->get_scripts() as $script => $priority)
        {
            if (! empty($script))
            {
                $html .= HTML::script($script)."\n";
            }
        }
        return $html;
    }

    private function _render_styles()
    {
        $html = '';
        foreach ($this->get_styles() as $style => $priority)
        {
            if (! empty($style))
            {
                $html .= HTML::style($style)."\n";
            }
        }
        return $html;
    }

    private function _render_inlineJS()
    {
        $html = '';
        if (isset($this->_namespaces_list[self::$namespace]['inlineJS']))
        {
            foreach ($this->_namespaces_list[self::$namespace]['inlineJS'] as $inlineScript)
            {
                $html .= ($inlineScript)."\n";
            }
        }
        return $html;
    }

    private function _render_inlineCSS()
    {
        $html = '';
        if (isset($this->_namespaces_list[self::$namespace]['inlineCSS']))
        {
            foreach ($this->_namespaces_list[self::$namespace]['inlineCSS'] as $inlineStyle)
            {
                $html .= ($inlineStyle)."\n";
            }
        }
        return $html;
    }

    function __toString()
    {
        return $this->compile();
    }
}
