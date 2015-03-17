<?php

/* list.tmp.html */
class __TwigTemplate_7903fea19be751af0d72f1df7f938fd23c433a705a9f381798cd9eaee2e9997e extends Twig_Template
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
\t<h2>List Messages</h2>
\t<table class=\"ink-table alternating\">
\t\t<thead>
\t\t\t\t<tr>
\t\t\t\t\t<th class=\"align-left\">To</th>
\t\t\t\t\t<th class=\"align-left\">Subject</th>
\t\t\t\t\t<th class=\"align-left\">Creeated</th>
\t\t\t\t\t<th class=\"align-left\">Read</th>
\t\t\t\t\t<th class=\"align-left\">Controls</th>
\t\t\t\t</tr>
\t\t</thead>
\t\t<tbody>
\t\t\t";
        // line 18
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["messages"]) ? $context["messages"] : null));
        foreach ($context['_seq'] as $context["_key"] => $context["message"]) {
            // line 19
            echo "\t\t\t\t<tr>
\t\t\t\t\t<td>rich@apewave.com</td>
\t\t\t\t\t<td>";
            // line 21
            echo twig_escape_filter($this->env, $this->getAttribute($context["message"], "Subject", array()));
            echo "</td>
\t\t\t\t\t<td>";
            // line 22
            echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute($context["message"], "CreatedGMT", array()), "F jS \\a\\t g:ia"), "html", null, true);
            echo "</td>
\t\t\t\t\t<td>";
            // line 23
            echo twig_escape_filter($this->env, $this->getAttribute($context["message"], "ReadGMT", array()));
            echo "</td>
\t\t\t\t\t<td class=\"controls\">
\t\t\t\t\t\t<a href=\"";
            // line 25
            echo twig_escape_filter($this->env, $this->getAttribute($context["message"], "MsgID", array()), "html", null, true);
            echo "\" class=\"edit\"><i class=\"fa fa-2x fa-pencil-square-o\"></i></a>
\t\t\t\t\t\t<a href=\"";
            // line 26
            echo twig_escape_filter($this->env, $this->getAttribute($context["message"], "MsgID", array()), "html", null, true);
            echo "\" class=\"delete\"><i class=\"fa fa-2x fa-trash-o\"></i></a>
\t\t\t\t\t</td>
\t\t\t\t</tr>
\t\t\t";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['message'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 30
        echo "\t\t</tbody>
\t</table>
</div>

<script>
Ink.requireModules(['Ink.Dom.Selector_1', 'Ink.Dom.Event_1', 'Ink.Net.Ajax_1'], function(InkSelector, InkEvent, Ajax) {
    
    var eds  = InkSelector.select('a.edit');
    eds.forEach(function(e)
    {
\t\tInkEvent.on(e, 'click', function(event) {
\t\t\tInkEvent.stopDefault(event);
\t\t\tvar t = InkEvent.findElement(event, 'a');
\t\t\tvar b = t.href.split('/');
\t\t\tvar MsgID = b.pop();
\t\t\tdocument.location = '/admin/edit/'+MsgID;
\t\t}); 
    });

    var dels = InkSelector.select('a.delete');
    dels.forEach(function(d)
    {
\t\tInkEvent.on(d, 'click', function(event) {
\t\t\tInkEvent.stopDefault(event);
\t\t\tvar t = InkEvent.findElement(event, 'a');
\t\t\tvar b = t.href.split('/');
\t\t\tvar MsgID = b.pop();
\t\t\t
\t\t\tvar uri = '/messages/'+MsgID;
\t\t\tnew Ajax(uri, {
\t\t\t\tmethod: 'DELETE',
\t\t\t\tpostBody: '',
\t\t\t\tonSuccess: function(xhrObj, req) {
\t\t\t\t\tInk.log(xhrObj.responseJSON);
\t\t\t\t\tdocument.location.reload();
\t\t\t\t}, 
\t\t\t\tonFailure: function() {
\t\t\t\t\tInk.log('Request failed');
\t\t\t\t},
\t\t\t\ton404: function() {
\t\t\t\t\tInk.log('404 request');
\t\t\t\t}, 
\t\t\t\tonTimeout: function() {
\t\t\t\t\tInk.log('Request timeout');
\t\t\t\t},
\t\t\t\ttimeout: 5
\t\t\t});
\t\t}); 
    });
});
</script>

";
    }

    public function getTemplateName()
    {
        return "list.tmp.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  90 => 30,  80 => 26,  76 => 25,  71 => 23,  67 => 22,  63 => 21,  59 => 19,  55 => 18,  39 => 4,  36 => 3,  11 => 1,);
    }
}
