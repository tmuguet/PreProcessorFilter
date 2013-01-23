<?php

/**
 * Else directive
 * 
 * @author Thomas Muguet <t.muguet@thomasmuguet.info>
 */
class PreProcessorDirectiveElse extends PreProcessorDirective
{

    /**
     * Initializes a new else
     * @param PreProcessorDirective $parent Parent directive (If or Elif)
     * @throws Exception $parent is not an instance of PreProcessorDirectiveIf or PreProcessorDirectiveElif
     */
    public function __construct(PreProcessorDirective &$parent)
    {
        $parentIf = NULL;
        if ($parent instanceof PreProcessorDirectiveIf) {
            $parentIf = $parent;
        } else if ($parent instanceof PreProcessorDirectiveElif) {
            $parentIf = $parent->parent;
        } else {
            throw new Exception("Unrecognized parent: " . get_class($parent));
        }
        $this->parent = $parentIf;
        $parentIf->addElse($this);
    }

    /**
     * Processes this directive. This should not be called.
     * 
     * @param PreProcessorContext $context Context
     * @return array Array of strings
     * @throw Exception Always
     */
    function process(PreProcessorContext &$context)
    {
        throw new Exception("Not implemened");
    }
}