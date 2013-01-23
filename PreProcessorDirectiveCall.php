<?php

/**
 * Macro call
 * 
 * @author Thomas Muguet <t.muguet@thomasmuguet.info>
 */
class PreProcessorDirectiveCall extends PreProcessorDirective
{

    /**
     * Name of the macro
     * @var string
     */
    public $name = NULL;

    /**
     * Calling arguments of the macro
     * @var array
     */
    public $args = array();

    /**
     * Initializes a new macro call
     * @param PreProcessorDirective $parent Parent directive
     * @param string $name Name of the macro
     * @param array $args Calling arguments of the macro
     */
    public function __construct(PreProcessorDirective &$parent, $name, $args)
    {
        parent::__construct($parent);
        $this->name = $name;
        $this->args = $args;
    }

    /**
     * Processes this directive
     * 
     * @param PreProcessorContext $context Context
     * @return array Array of strings
     * @throws Exception Macro not defined
     */
    public function process(\PreProcessorContext &$context)
    {
        if (!key_exists($context->macros, $this->name)) {
            throw new Exception("Macro " . $this->name . " is not defined");
        }

        return array($context->macros[$this->name]->call($this->args));
    }
}