<?php

/* base.tmp.html */
class __TwigTemplate_c6d3cd45dd86fe355ccd29415c2a9ff1553674a748e919ff90e86e139cd0fe08 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'content' => array($this, 'block_content'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!DOCTYPE html>
<html lang=\"en\">

    <head>
        <meta charset=\"utf-8\">
        <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge,chrome=1\">
        <title>Message Queue Server Admin</title>
        <meta name=\"description\" content=\"\">
        <meta name=\"author\" content=\"ink, cookbook, recipes\">
        <meta name=\"HandheldFriendly\" content=\"True\">
        <meta name=\"MobileOptimized\" content=\"320\">
        <meta name=\"mobile-web-app-capable\" content=\"yes\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0\">

        <!-- Place favicon.ico and apple-touch-icon(s) here  -->

        <link rel=\"shortcut icon\" href=\"/static/ink/dist/img/favicon.ico\">
        <link rel=\"apple-touch-icon\" href=\"/static/ink/dist/img/touch-icon-iphone.png\">
        <link rel=\"apple-touch-icon\" sizes=\"76x76\" href=\"/static/ink/dist/img/touch-icon-ipad.png\"> 
        <link rel=\"apple-touch-icon\" sizes=\"120x120\" href=\"/static/ink/dist/img/touch-icon-iphone-retina.png\">
        <link rel=\"apple-touch-icon\" sizes=\"152x152\" href=\"/static/ink/dist/img/touch-icon-ipad-retina.png\">
        <link rel=\"apple-touch-startup-image\" href=\"/static/ink/dist/img/splash.320x460.png\" media=\"screen and (min-device-width: 200px) and (max-device-width: 320px) and (orientation:portrait)\">
        <link rel=\"apple-touch-startup-image\" href=\"/static/ink/dist/img/splash.768x1004.png\" media=\"screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:portrait)\">
        <link rel=\"apple-touch-startup-image\" href=\"/static/ink/dist/img/splash.1024x748.png\" media=\"screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:landscape)\">

        <!-- load Ink's css -->
        <link rel=\"stylesheet\" type=\"text/css\" href=\"/static/ink/dist/css/ink-flex.min.css\">
        <link rel=\"stylesheet\" type=\"text/css\" href=\"/static/ink/dist/css/font-awesome.min.css\">
        <link rel=\"stylesheet\" type=\"text/css\" href=\"/static/css/mqs.css\">

        <!-- load Ink's css for IE8 -->
        <!--[if lt IE 9 ]>
            <link rel=\"stylesheet\" href=\"/static/ink/dist/css/ink-ie.min.css\" type=\"text/css\" media=\"screen\" title=\"no title\" charset=\"utf-8\">
        <![endif]-->

        <!-- test browser flexbox support and load legacy grid if unsupported -->
        <script type=\"text/javascript\" src=\"/static/ink/dist/js/modernizr.js\"></script>
        <script type=\"text/javascript\">
            Modernizr.load({
              test: Modernizr.flexbox,
              nope : '/static/ink/dist/css/ink-legacy.min.css'
            });
        </script>

        <!-- load Ink's javascript -->
        <script type=\"text/javascript\" src=\"/static/ink/dist/js/holder.js\"></script>
        <script type=\"text/javascript\" src=\"/static/ink/dist/js/ink-all.min.js\"></script>
        <script type=\"text/javascript\" src=\"/static/ink/dist/js/autoload.js\"></script>

    </head>

    <body>
        <div class=\"ink-grid\">

            <header>
                <h1>MQS<small>Hello ";
        // line 56
        echo twig_escape_filter($this->env, (isset($context["name"]) ? $context["name"] : null), "html", null, true);
        echo ", this is the Message Queue Server's admin area</small></h1>
                <nav class=\"ink-navigation\">
                    <ul class=\"menu horizontal orange\">
                        <li";
        // line 59
        if (((isset($context["v"]) ? $context["v"] : null) == "create")) {
            echo " class=\"active\"";
        }
        echo "><a href=\"/admin/create\">Create Message</a></li>
                        <li";
        // line 60
        if (((isset($context["v"]) ? $context["v"] : null) == "list")) {
            echo " class=\"active\"";
        }
        echo "><a href=\"/admin/list\">List Messages</a></li>
                    </ul>
                </nav>
            </header>
            
\t\t\t";
        // line 65
        $this->displayBlock('content', $context, $blocks);
        // line 66
        echo "\t\t\t
\t\t</div>

    </body>
</html>
";
    }

    // line 65
    public function block_content($context, array $blocks = array())
    {
    }

    public function getTemplateName()
    {
        return "base.tmp.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  110 => 65,  101 => 66,  99 => 65,  89 => 60,  83 => 59,  77 => 56,  20 => 1,);
    }
}
