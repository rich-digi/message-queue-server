<?php

/* index.html */
class __TwigTemplate_b02cd94e48a49351ca52e020c829208b8407f7c65bbafb9187a8c062bab911d2 extends Twig_Template
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
<html>
    <head>
        <meta charset=\"utf-8\"/>
        <title>Message Queue Server Admin</title>
        <style>
            html,body,div,span,object,iframe,
            h1,h2,h3,h4,h5,h6,p,blockquote,pre,
            abbr,address,cite,code,
            del,dfn,em,img,ins,kbd,q,samp,
            small,strong,sub,sup,var,
            b,i,
            dl,dt,dd,ol,ul,li,
            fieldset,form,label,legend,
            table,caption,tbody,tfoot,thead,tr,th,td,
            article,aside,canvas,details,figcaption,figure,
            footer,header,hgroup,menu,nav,section,summary,
            time,mark,audio,video{margin:0;padding:0;border:0;outline:0;font-size:100%;vertical-align:baseline;background:transparent;}
            body{line-height:1;}
            article,aside,details,figcaption,figure,
            footer,header,hgroup,menu,nav,section{display:block;}
            nav ul{list-style:none;}
            blockquote,q{quotes:none;}
            blockquote:before,blockquote:after,
            q:before,q:after{content:'';content:none;}
            a{margin:0;padding:0;font-size:100%;vertical-align:baseline;background:transparent;}
            ins{background-color:#ff9;color:#000;text-decoration:none;}
            mark{background-color:#ff9;color:#000;font-style:italic;font-weight:bold;}
            del{text-decoration:line-through;}
            abbr[title],dfn[title]{border-bottom:1px dotted;cursor:help;}
            table{border-collapse:collapse;border-spacing:0;}
            hr{display:block;height:1px;border:0;border-top:1px solid #cccccc;margin:1em 0;padding:0;}
            input,select{vertical-align:middle;}
            html{ background: #EDEDED; height: 100%; }
            body{background:#FFF;margin:0 auto;min-height:100%;padding:0 30px;width:60em;color:#666;font:14px/23px Arial,Verdana,sans-serif;}
            h1,h2,h3,p,ul,ol,form,section{margin:0 0 20px 0;}
            h1{color:#333;font-size:20px;}
            h2,h3{color:#333;font-size:14px;}
            h3{margin:0;font-size:12px;font-weight:bold;}
            ul,ol{list-style-position:inside;color:#999;}
            ul{list-style-type:square;}
            code,kbd{background:#EEE;border:1px solid #DDD;border:1px solid #DDD;border-radius:4px;-moz-border-radius:4px;-webkit-border-radius:4px;padding:0 4px;color:#666;font-size:12px;}
            pre{background:#EEE;border:1px solid #DDD;border-radius:4px;-moz-border-radius:4px;-webkit-border-radius:4px;padding:5px 10px;color:#666;font-size:12px;}
            pre code{background:transparent;border:none;padding:0;}
            a{color:#70a23e;}
            header{padding: 30px 0;text-align:center;}
        </style>
    </head>
    <body>
        <header>
        </header>
        <h1>Welcome to MQS, ";
        // line 52
        echo twig_escape_filter($this->env, (isset($context["name"]) ? $context["name"] : null), "html", null, true);
        echo "</h1>
        <p>
        It's good to message.
        </p>
        <section>
        </section>
    </body>
</html>
";
    }

    public function getTemplateName()
    {
        return "index.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  72 => 52,  19 => 1,);
    }
}