Kohana-Assets-Module
====================

JS/CSS managment module for Kohana 3.2

## Exaples
in you Controller extending of Controller_Template put some like this: 
```php
class Controller_Abstract extends Controller_Template
{
    public $template = 'layouts/main';

    function before()
    {
        parent::before();
        if ($this->auto_render)
        {
            $this->template->content = '';

            $styles = array(
                'style.css'
            );
            $scripts = array(
                'jquery-ui-1.8.23.custom.min.js',
                'jquery.easing.min.1.3.js',
                'iScroll.min.js'
            );
            Assets::instance('frontend')
                ->addStyles($styles, 'media/assets/css/')
                ->addScripts('https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js')
                ->addScripts($scripts, 'media/assets/js/lib/')
                ->addScripts('media/assets/js/main.js');
        }
    }
```
<br />
In you 'layouts/main.php' file add:
```html
<!DOCTYPE html>
<html>
<head>
    <title>Cool site</title>
    <?php Assets::instance('frontend')->render(); ?>
</head>
<body>
    Lorem ipsum...
</body>
</html>
```