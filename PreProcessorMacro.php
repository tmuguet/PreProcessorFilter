<?php

/**
 * Macro for the pre-processor
 * @author Thomas Muguet <t.muguet@thomasmuguet.info>
 */
class PreProcessorMacroStart
{

    /**
     * Name of the macro
     * @var string 
     */
    public $name      = NULL;

    /**
     * Variable names
     * @var array 
     */
    public $variables = array();

    /**
     * Content of the macro
     * @var string
     */
    public $content = NULL;

    /**
     * Iniitalizes a new macro
     * @param string $name Name of the macro
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Adds a variable
     * @param string $name Name of the macro
     * @return \PreProcessorMacroStart This
     */
    public function addVariable($name)
    {
        $this->variables[] = '{' . $name . '}';
        return $this;
    }

    /**
     * Sets the macro content.
     * 
     * Variable foo will be replaced using {foo}.
     * 
     * @param string $content
     * @return \PreProcessorMacroStart This
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Calls the macro
     * @param array $args List of arguments
     * @return string Processed macro content
     */
    public function call($args)
    {
        return str_replace($this->variables, $args, $this->content);
    }
}