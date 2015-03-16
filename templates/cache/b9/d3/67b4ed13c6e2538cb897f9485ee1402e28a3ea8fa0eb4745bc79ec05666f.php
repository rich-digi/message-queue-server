<?php

/* edit.tmp.html */
class __TwigTemplate_b9d367b4ed13c6e2538cb897f9485ee1402e28a3ea8fa0eb4745bc79ec05666f extends Twig_Template
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
\t\t<form action=\"\" id=\"create_message\" class=\"ink-form all-100 small-100 tiny-100\">
\t\t<input type=\"hidden\" name=\"MsgID\" value=\"";
        // line 9
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["message"]) ? $context["message"] : null), "MsgID", array()), "html", null, true);
        echo "\">
\t\t<div class=\"control-group required column-group gutters\">
\t\t\t<label for=\"email\" class=\"all-20 align-right\">To</label>
\t\t\t<div class=\"control all-80\">
\t\t\t\t<input type=\"text\" name=\"ToDMID\" value=\"";
        // line 13
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["message"]) ? $context["message"] : null), "ToDMID", array()), "html", null, true);
        echo "\">
\t\t\t</div>
\t\t</div>
\t\t<div class=\"control-group required column-group gutters\">
\t\t\t<label for=\"email\" class=\"all-20 align-right\">Subject</label>
\t\t\t<div class=\"control all-80\">
\t\t\t\t<input type=\"text\" name=\"Subject\" value=\"";
        // line 19
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["message"]) ? $context["message"] : null), "Subject", array()), "html", null, true);
        echo "\">
\t\t\t</div>
\t\t</div>
\t\t<div class=\"control-group required column-group gutters\">
\t\t\t<label for=\"email\" class=\"all-20 align-right\">From</label>
\t\t\t<div class=\"control all-80\">
\t\t\t\t<input type=\"text\" name=\"From\" value=\"";
        // line 25
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["message"]) ? $context["message"] : null), "From", array()), "html", null, true);
        echo "\">
\t\t\t</div>
\t\t</div>
\t\t<div class=\"control-group column-group gutters\">
\t\t\t<label for=\"email\" class=\"all-20 align-right\">ReplyTo</label>
\t\t\t<div class=\"control all-80\">
\t\t\t\t<input type=\"text\" name=\"ReplyTo\" value=\"";
        // line 31
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["message"]) ? $context["message"] : null), "ReplyTo", array()), "html", null, true);
        echo "\">
\t\t\t</div>
\t\t</div>
\t\t<div class=\"control-group column-group gutters\">
\t\t\t<label for=\"email\" class=\"all-20 align-right\">Priority</label>
\t\t\t<div class=\"control all-80\">
\t\t\t\t<input type=\"text\" name=\"Priority\" value=\"";
        // line 37
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["message"]) ? $context["message"] : null), "Priority", array()), "html", null, true);
        echo "\">
\t\t\t</div>
\t\t</div>
\t\t<div class=\"control-group required column-group gutters\">
\t\t\t<label for=\"area\" class=\"all-20 align-right\">Content</label>
\t\t\t<div  class=\"control all-80\">
\t\t\t\t<textarea name=\"Content\" rows=\"10\">";
        // line 43
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["message"]) ? $context["message"] : null), "Content", array()), "html", null, true);
        echo "</textarea>
\t\t\t</div>
\t\t</div>
\t\t <div class=\"control-group column-group gutters\">
\t\t\t<div class=\"all-20\"></div>
\t\t\t<div class=\"control all-80\">
\t\t\t\t<button type=\"submit\" class=\"ink-button orange\">Edit Message</button>
\t\t\t</div>
\t\t</div>
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
\t\tInk.log(f); 
\t\tvar uri = '/messages/'+f.MsgID;
\t\tdelete f.MsgID;
\t\tInk.log(uri, f); 
\t\t
\t\tnew Ajax(uri, {
\t\t\tmethod: 'PUT',
\t\t\tpostBody: JSON.stringify(f),
\t\t\tonSuccess: function(xhrObj, req) {
\t\t\t\tvar r = xhrObj.responseJSON;
\t\t\t\tif (r.Updated)
\t\t\t\t{
\t\t\t\t\talert('Message Updated');
\t\t\t\t\tdocument.location.href = '/admin/list';
\t\t\t\t}
\t\t\t\telse
\t\t\t\t{
\t\t\t\t\talert('Update failed');
\t\t\t\t}
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
        return "edit.tmp.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  98 => 43,  89 => 37,  80 => 31,  71 => 25,  62 => 19,  53 => 13,  46 => 9,  39 => 4,  36 => 3,  11 => 1,);
    }
}
