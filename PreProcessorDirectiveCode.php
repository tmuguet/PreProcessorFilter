<?php

/**
 * Native code
 * 
 * @author Thomas Muguet <t.muguet@thomasmuguet.info>
 */
class PreProcessorDirectiveCode extends PreProcessorDirective
{

    /**
     * Line of code
     * @var string 
     */
    protected $line = NULL;

    /**
     * Initializes a new line
     * @param PreProcessorDirective $parent Parent directive
     * @param string $line Line of code
     */
    public function __construct(PreProcessorDirective &$parent, $line)
    {
        parent::__construct($parent);
        $this->line = $line;
    }

    /**
     * Processes this directive
     * 
     * @param PreProcessorContext $context Context
     * @return array Array of strings
     */
    public function process(\PreProcessorContext &$context)
    {
        if ($this->line === NULL) {
            return array();
        } else {
            return array($this->line);
        }
    }
}