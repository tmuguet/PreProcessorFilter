<?php

/**
 * Elif directive
 * 
 * @author Thomas Muguet <t.muguet@thomasmuguet.info>
 */
class PreProcessorDirectiveElif extends PreProcessorDirective
{

    /**
     * Constant tested
     * @var string
     */
    protected $constant = NULL;

    /**
     * Initializes a new elif
     * @param PreProcessorDirective $parent Parent directive (If or Elif)
     * @param string $constant Name of the constant tested
     * @throws Exception $parent is not an instance of PreProcessorDirectiveIf or PreProcessorDirectiveElif
     */
    public function __construct(PreProcessorDirective &$parent, $constant)
    {
        $parentIf = NULL;
        if ($parent instanceof PreProcessorDirectiveIf) {
            $parentIf = $parent;
        } else if ($parent instanceof PreProcessorDirectiveElif) {
            $parentIf = $parent->getParent();
        } else {
            throw new Exception("Unrecognized parent: " . get_class($parent));
        }
        $this->parent   = $parentIf;
        $parentIf->addElif($this);
        $this->constant = $constant;
    }

    /**
     * Processes this directive
     * 
     * @param PreProcessorContext $context Context
     * @return array Array of strings
     */
    public function process(\PreProcessorContext &$context)
    {
        return parent::processNative($context);
    }

    /**
     * Evaluates this directive
     * 
     * @param PreProcessorContext $context Context
     * @return bool True if the constant is defined and equals to TRUE
     */
    public function evaluate(PreProcessorContext &$context)
    {
        return $context->hasDefinition($this->constant)
                && $context->getDefinition($this->constant);
    }
}