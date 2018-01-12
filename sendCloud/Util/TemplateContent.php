<?php

namespace SendCloud\Util;

class TemplateContent
{
    private $template_vars;
    private $template_invoke_name;
    
    public function getTemplateVars()
    {
        return $this->template_vars;
    }
    
    public function addVars($key, $value = [])
    {
        $this->template_vars[$key] = $value;
    }
    
    public function getTemplateInvokeName()
    {
        return $this->template_invoke_name;
    }
    
    public function setTemplateInvokeName($invoke_name)
    {
        $this->template_invoke_name = $invoke_name;
    }
}
