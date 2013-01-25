<?php

/**
 * Pre-processor directive
 * 
 * @author Thomas Muguet <t.muguet@thomasmuguet.info>
 */
abstract class PreProcessorDirective
{

    /**
     * Parent of this directive
     * @var PreProcessorDirective 
     */
    protected $parent    = NULL;
    /**
     * Sub-blocks of the directive
     * @var array 
     */
    protected $subblocks = array();

    /**
     * Initializes a new directive
     * @param PreProcessorDirective $parent Parent directive
     */
    public function __construct(PreProcessorDirective &$parent)
    {
        $this->parent = $parent;
        $parent->addSubBlock($this);
    }
    
    public function getParent() {
        return $this->parent;
    }

    /**
     * Native processing
     * 
     * @param PreProcessorContext $context Context
     * @return array Array of strings
     */
    public function processNative(PreProcessorContext &$context)
    {
        $lines = array();
        foreach ($this->subblocks as $subblock) {
            $processed = $subblock->process($context);
            if (!empty($processed)) {
                $lines = array_merge($lines, $processed);
            }
        }
        return $lines;
    }

    /**
     * Adds a sub-block
     * @param PreProcessorDirective $subblock Sub-block
     * @return PreProcessorDirective This
     */
    public function addSubBlock(PreProcessorDirective &$subblock)
    {
        $this->subblocks[] = $subblock;
        return $this;
    }

    /**
     * Processes this directive
     * 
     * @param PreProcessorContext $context Context
     * @return array Array of strings
     */
    public abstract function process(PreProcessorContext &$context);
}