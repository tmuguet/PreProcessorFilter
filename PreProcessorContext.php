<?php

/**
 * Context for the pre-processor
 * @author Thomas Muguet <t.muguet@thomasmuguet.info>
 */
class PreProcessorContext
{

    /**
     * Definitions
     * @var array 
     */
    protected $definitions = array();

    /**
     * Macros
     * @var array 
     */
    public $macros = array();
    
    public function __construct()
    {
        $this->definitions['TRUE'] = TRUE;
        $this->definitions['FALSE'] = FALSE;
    }

    public function addDefinition($name, $value)
    {
        $this->definitions[$name] = $value;
    }

    public function hasDefinition($name)
    {
        return array_key_exists($name, $this->definitions);
    }

    public function getDefinition($name)
    {
        return $this->definitions[$name];
    }

}
