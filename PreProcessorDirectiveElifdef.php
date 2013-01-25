<?php

/**
 * Elifdef directive
 * 
 * @author Thomas Muguet <t.muguet@thomasmuguet.info>
 */
class PreProcessorDirectiveElifdef extends PreProcessorDirectiveElif
{

    /**
     * Evaluates this directive
     * 
     * @param PreProcessorContext $context Context
     * @return bool True if the constant is defined
     */
    public function evaluate(PreProcessorContext &$context)
    {
        return $context->hasDefinition($this->constant);
    }
}

/**
 * Elifndef directive
 * 
 * @author Thomas Muguet <t.muguet@thomasmuguet.info>
 */
class PreProcessorDirectiveElifndef extends PreProcessorDirectiveElif
{

    /**
     * Evaluates this directive
     * 
     * @param PreProcessorContext $context Context
     * @return bool True if the constant is not defined
     */
    public function evaluate(PreProcessorContext &$context)
    {
        return !$context->hasDefinition($this->constant);
    }
}