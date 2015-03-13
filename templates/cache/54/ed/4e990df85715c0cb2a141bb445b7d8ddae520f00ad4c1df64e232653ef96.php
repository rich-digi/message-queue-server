<?php

/* index.tmp.html */
class __TwigTemplate_54ed4e990df85715c0cb2a141bb445b7d8ddae520f00ad4c1df64e232653ef96 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
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

        <link rel=\"shortcut icon\" href=\"http://cdn.ink.sapo.pt/3.1.1/img/favicon.ico\">
        <link rel=\"apple-touch-icon\" href=\"http://cdn.ink.sapo.pt/3.1.1/img/touch-icon-iphone.png\">
        <link rel=\"apple-touch-icon\" sizes=\"76x76\" href=\"http://cdn.ink.sapo.pt/3.1.1/img/touch-icon-ipad.png\"> 
        <link rel=\"apple-touch-icon\" sizes=\"120x120\" href=\"http://cdn.ink.sapo.pt/3.1.1/img/touch-icon-iphone-retina.png\">
        <link rel=\"apple-touch-icon\" sizes=\"152x152\" href=\"http://cdn.ink.sapo.pt/3.1.1/img/touch-icon-ipad-retina.png\">
        <link rel=\"apple-touch-startup-image\" href=\"http://cdn.ink.sapo.pt/3.1.1/img/splash.320x460.png\" media=\"screen and (min-device-width: 200px) and (max-device-width: 320px) and (orientation:portrait)\">
        <link rel=\"apple-touch-startup-image\" href=\"http://cdn.ink.sapo.pt/3.1.1/img/splash.768x1004.png\" media=\"screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:portrait)\">
        <link rel=\"apple-touch-startup-image\" href=\"http://cdn.ink.sapo.pt/3.1.1/img/splash.1024x748.png\" media=\"screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:landscape)\">

        <!-- load Ink's css from the cdn -->
        <link rel=\"stylesheet\" type=\"text/css\" href=\"http://cdn.ink.sapo.pt/3.1.1/css/ink-flex.min.css\">
        <link rel=\"stylesheet\" type=\"text/css\" href=\"http://cdn.ink.sapo.pt/3.1.1/css/font-awesome.min.css\">
        <link rel=\"stylesheet\" type=\"text/css\" href=\"/static/css/test.css\">

        <!-- load Ink's css for IE8 -->
        <!--[if lt IE 9 ]>
            <link rel=\"stylesheet\" href=\"http://cdn.ink.sapo.pt/3.1.1/css/ink-ie.min.css\" type=\"text/css\" media=\"screen\" title=\"no title\" charset=\"utf-8\">
        <![endif]-->

        <!-- test browser flexbox support and load legacy grid if unsupported -->
        <script type=\"text/javascript\" src=\"http://cdn.ink.sapo.pt/3.1.1/js/modernizr.js\"></script>
        <script type=\"text/javascript\">
            Modernizr.load({
              test: Modernizr.flexbox,
              nope : 'http://cdn.ink.sapo.pt/3.1.1/css/ink-legacy.min.css'
            });
        </script>

        <!-- load Ink's javascript files from the cdn -->
        <script type=\"text/javascript\" src=\"http://cdn.ink.sapo.pt/3.1.1/js/holder.js\"></script>
        <script type=\"text/javascript\" src=\"http://cdn.ink.sapo.pt/3.1.1/js/ink-all.min.js\"></script>
        <script type=\"text/javascript\" src=\"http://cdn.ink.sapo.pt/3.1.1/js/autoload.js\"></script>


        <style type=\"text/css\">

            header {
                padding: 2em 0;
                margin-bottom: 2em;
            }
            header h1 {
                font-size: 2em;
            }
            header h1 small:before  {
                content: \"|\";
                margin: 0 0.5em;
                font-size: 1.8em;
            }
            footer {
                background: #ccc;
            }

        </style>

    </head>

    <body>
        <div class=\"ink-grid\">

            <!--[if lte IE 9 ]>
            <div class=\"ink-alert basic\" role=\"alert\">
                <button class=\"ink-dismiss\">&times;</button>
                <p>
                    <strong>You are using an outdated Internet Explorer version.</strong>
                    Please <a href=\"http://browsehappy.com/\">upgrade to a modern browser</a> to improve your web experience.
                </p>
            </div>
            -->

            <!-- Add your site or application content here -->

            <header>
                <h1>MQS<small>Hello ";
        // line 89
        echo twig_escape_filter($this->env, (isset($context["name"]) ? $context["name"] : null), "html", null, true);
        echo ", this is the Message Queue Server's admin area</small></h1>
                <nav class=\"ink-navigation\">
                    <ul class=\"menu horizontal orange\">
                        <li class=\"active\"><a href=\"#\">Create Message</a></li>
                        <li><a href=\"#\">List Messages</a></li>
                    </ul>
                </nav>
            </header>
            
            <div class=\"column-group gutters\">
                <form action=\"\" id=\"create_message\" class=\"ink-form all-50 small-100 tiny-100\">
                    <fieldset>
                        <div class=\"control-group required column-group gutters\">
                            <label for=\"email\" class=\"all-20 align-right\">To</label>
                            <div class=\"control all-80\">
                                <input type=\"text\" name=\"ToDMID\">
                            </div>
                        </div>
                        <div class=\"control-group required column-group gutters\">
                            <label for=\"email\" class=\"all-20 align-right\">Subject</label>
                            <div class=\"control all-80\">
                                <input type=\"text\" name=\"Subject\">
                            </div>
                        </div>
                        <div class=\"control-group required column-group gutters\">
                            <label for=\"area\" class=\"all-20 align-right\">Content</label>
                            <div  class=\"control all-80\">
                                <textarea name=\"Content\"></textarea>
                            </div>
                        </div>
                         <div class=\"control-group column-group gutters\">
                            <div class=\"all-20\"></div>
                            <div class=\"control all-80\">
                                <button type=\"submit\" class=\"ink-button orange\">Create Message</button>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>

        </div>

\t\t<script>
\t\t
\t\tInk.requireModules(['Ink.Net.Ajax_1', 'Ink.Dom.Event_1', 'Ink.Dom.FormSerialize_1'], function(Ajax, InkEvent,  FormSerialize) {
\t\t\tvar form = Ink.i('create_message');
\t\t\tInkEvent.observe(form, 'submit', function(event)
\t\t\t{
\t\t\t\tInkEvent.stopDefault(event);
\t\t\t\tvar oSerialize = FormSerialize.serialize(form);
\t\t\t\tvar uri = '/messages/'+oSerialize.ToDMID;
\t\t\t\tdelete oSerialize.ToDMID;
\t\t\t\tInk.log(uri, oSerialize); 
\t\t\t\t
\t\t\t\tnew Ajax(uri, {
\t\t\t\t\tmethod: 'PUT',
\t\t\t\t\tpostBody: JSON.stringify(oSerialize),
\t\t\t\t\tonInit: function(obj) {
\t\t\t\t\t\tInk.log('Init request');
\t\t\t\t\t}, 
\t\t\t\t\tonComplete: function() {
\t\t\t\t\t\tInk.log('Request completed');
\t\t\t\t\t},
\t\t\t\t\tonFailure: function() {
\t\t\t\t\t\tInk.log('Request failed');
\t\t\t\t\t},
\t\t\t\t\ton404: function() {
\t\t\t\t\t\tInk.log('404 request');
\t\t\t\t\t}, 
\t\t\t\t\tonTimeout: function() {
\t\t\t\t\t\tInk.log('Request timeout');
\t\t\t\t\t},
\t\t\t\t\ttimeout: 5
\t\t\t\t});

\t\t\t});
\t\t});
\t\t
\t\t</script>

    </body>
</html>
";
    }

    public function getTemplateName()
    {
        return "index.tmp.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  109 => 89,  19 => 1,);
    }
}
