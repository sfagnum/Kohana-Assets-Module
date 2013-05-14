Kohana-Assets-Module
====================

JS/CSS managment module for Kohana 3.3

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
                ->add_styles($styles, 'media/assets/css/')
                ->add_scripts('https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js')
                ->add_scripts($scripts, 'media/assets/js/lib/')
                ->add_scripts('media/assets/js/main.js');
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
    <?php Assets::instance('frontend')->render(Assets::STYLES); ?>
</head>
<body>
    Lorem ipsum...
    <?php Assets::instance('frontend')->render(Assets::SCRIPTS); ?>
</body>
</html>
```