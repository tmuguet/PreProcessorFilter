<?php

/**
 * Ifdef directive
 * 
 * @author Thomas Muguet <t.muguet@thomasmuguet.info>
 */
class PreProcessorDirectiveIfdef extends PreProcessorDirectiveIf
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
 * Ifndef directive
 * 
 * @author Thomas Muguet <t.muguet@thomasmuguet.info>
 */
class PreProcessor_Directive_Ifndef extends PreProcessorDirectiveIfdef
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