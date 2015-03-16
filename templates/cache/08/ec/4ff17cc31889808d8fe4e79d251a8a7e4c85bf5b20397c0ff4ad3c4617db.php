<?php

/* create.tmp.html */
class __TwigTemplate_08ec4ff17cc31889808d8fe4e79d251a8a7e4c85bf5b20397c0ff4ad3c4617db extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        try {
            $this->parent = $this->env->loadTemplate("base.tmp.html");
        } catch (Twig_Error_Loader $e) {
            $e->setTemplateFile($this->getTemplateName());
            $e->setTemplateLine(1);

            throw $e;
        }

        $this->blocks = array(
            'content' => array($this, 'block_content'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "base.tmp.html";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_content($context, array $blocks = array())
    {
        // line 4
        echo "
<div>

\t<div class=\"column-group gutters\">
\t\t<form action=\"\" id=\"create_message\" class=\"ink-form all-50 small-100 tiny-100\">
\t\t\t<fieldset>
\t\t\t\t<div class=\"control-group required column-group gutters\">
\t\t\t\t\t<label for=\"email\" class=\"all-20 align-right\">To</label>
\t\t\t\t\t<div class=\"control all-80\">
\t\t\t\t\t\t<input type=\"text\" name=\"ToDMID\">
\t\t\t\t\t</div>
\t\t\t\t</div>
\t\t\t\t<div class=\"control-group required column-group gutters\">
\t\t\t\t\t<label for=\"email\" class=\"all-20 align-right\">Subject</label>
\t\t\t\t\t<div class=\"control all-80\">
\t\t\t\t\t\t<input type=\"text\" name=\"Subject\">
\t\t\t\t\t</div>
\t\t\t\t</div>
\t\t\t\t<div class=\"control-group required column-group gutters\">
\t\t\t\t\t<label for=\"area\" class=\"all-20 align-right\">Content</label>
\t\t\t\t\t<div  class=\"control all-80\">
\t\t\t\t\t\t<textarea name=\"Content\"></textarea>
\t\t\t\t\t</div>
\t\t\t\t</div>
\t\t\t\t <div class=\"control-group column-group gutters\">
\t\t\t\t\t<div class=\"all-20\"></div>
\t\t\t\t\t<div class=\"control all-80\">
\t\t\t\t\t\t<button type=\"submit\" class=\"ink-button orange\">Create Message</button>
\t\t\t\t\t</div>
\t\t\t\t</div>
\t\t\t</fieldset>
\t\t</form>
\t</div>

</div>

<script>

Ink.requireModules(['Ink.Net.Ajax_1', 'Ink.Dom.Event_1', 'Ink.Dom.FormSerialize_1'], function(Ajax, InkEvent,  FormSerialize) {
\tvar form = Ink.i('create_message');
\tInkEvent.observe(form, 'submit', function(event)
\t{
\t\tInkEvent.stopDefault(event);
\t\tvar f = FormSerialize.serialize(form);
\t\tvar uri = '/messages/'+f.ToDMID;
\t\tdelete f.ToDMID;
\t\tInk.log(uri, f); 
\t\t
\t\tnew Ajax(uri, {
\t\t\tmethod: 'POST',
\t\t\tpostBody: JSON.stringify(f),
\t\t\tonSuccess: function(xhrObj, req) {
\t\t\t\tInk.log(xhrObj.responseJSON);
\t\t\t}, 
\t\t\tonFailure: function() {
\t\t\t\tInk.log('Request failed');
\t\t\t},
\t\t\ton404: function() {
\t\t\t\tInk.log('404 request');
\t\t\t}, 
\t\t\tonTimeout: function() {
\t\t\t\tInk.log('Request timeout');
\t\t\t},
\t\t\ttimeout: 5
\t\t});

\t});
});

</script>

";
    }

    public function getTemplateName()
    {
        return "create.tmp.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  39 => 4,  36 => 3,  11 => 1,);
    }
}
