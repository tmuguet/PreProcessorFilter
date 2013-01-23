<?php

/**
 * Root directive
 * 
 * @author Thomas Muguet <t.muguet@thomasmuguet.info>
 */
class PreProcessorDirectiveRoot extends PreProcessorDirective
{

    /**
     * Initializes a new root
     */
    public function __construct()
    {
        
    }

    /**
     * Processes this directive
     * 
     * @param PreProcessorContext $context Context
     * @return array Array of strings
     */
    public function process(\PreProcessorContext &$context)
    {
        return $this->processNative($context);
    }
}