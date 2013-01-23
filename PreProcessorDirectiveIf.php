<?php

/**
 * If directive
 * 
 * @author Thomas Muguet <t.muguet@thomasmuguet.info>
 */
class PreProcessorDirectiveIf extends PreProcessorDirective
{

    /**
     * List of corresponding Elif directives
     * @var array 
     */
    public $elif = array();

    /**
     * Corresponding else directive
     * @var PreProcessorDirectiveElse 
     */
    public $else     = NULL;

    /**
     * Constant tested
     * @var string
     */
    public $constant = NULL;

    /**
     * Initializes a new if
     * @param PreProcessorDirective $parent Parent directive
     * @param string $constant Name of the constant tested
     */
    public function __construct(PreProcessorDirective &$parent, $constant)
    {
        parent::__construct($parent);
        $this->constant = $constant;
    }

    /**
     * Adds a corresponding Elif directive
     * @param PreProcessorDirectiveElif $elif Elif directive
     * @return \PreProcessorDirectiveIf This
     */
    public function addElif(PreProcessorDirectiveElif &$elif)
    {
        $this->elif[] = $elif;
        return $this;
    }

    /**
     * Adds the corresponding Else directive
     * @param PreProcessorDirectiveElse $else Else directive
     * @return \PreProcessorDirectiveIf This
     */
    public function addElse(PreProcessorDirectiveElse &$else)
    {
        $this->else = $else;
        return $this;
    }

    /**
     * Processes this directive and corresponding Elsif / Endif
     * 
     * @param PreProcessorContext $context Context
     * @return array Array of strings
     */
    public function process(PreProcessorContext &$context)
    {
        $lines = array();
        if ($this->evaluate($context)) {
            $lines = array_merge($this->processNative($context));
        } else {
            $found = FALSE;
            foreach ($this->elif as $elif) {
                if (!$found && $elif->evaluate($context)) {
                    $lines = array_merge($elif->processNative($context));
                    $found = TRUE;
                }
            }
            if (!$found && $this->else !== NULL) {
                $lines = array_merge($this->else->processNative($context));
            }
        }
        return $lines;
    }

    /**
     * Evaluates this directive
     * 
     * @param PreProcessorContext $context Context
     * @return bool True if the constant is defined and equals to TRUE
     */
    public function evaluate(PreProcessorContext &$context)
    {
        return key_exists($this->constant, $context->definitions) &&
                $context->definitions[$this->constant];
    }
}