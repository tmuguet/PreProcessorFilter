<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL.
 */

require_once 'phing/filters/BaseParamFilterReader.php';
require_once 'phing/filters/ChainableReader.php';

require_once 'PreProcessorMacro.php';
require_once 'PreProcessorContext.php';
require_once 'PreProcessorDirective.php';
require_once 'PreProcessorDirectiveRoot.php';
require_once 'PreProcessorDirectiveCode.php';
require_once 'PreProcessorDirectiveIf.php';
require_once 'PreProcessorDirectiveIfdef.php';
require_once 'PreProcessorDirectiveElif.php';
require_once 'PreProcessorDirectiveElifdef.php';
require_once 'PreProcessorDirectiveElse.php';
require_once 'PreProcessorDirectiveCall.php';

/**
 * Pre-processor filter
 * 
 * Usage:
 * <filterchain>
 * <filterreader classname="path.to.filters.PreProcessorFilter">
 * <param name="DEBUG" value="" />
 * <param name="macrodir" value="/foo/bar" />
 * </filterreader>
 * </filterchain>
 *
 * @author Thomas Muguet <t.muguet@thomasmuguet.info>
 * @version 1.3.0
 */
class PreProcessorFilter extends BaseParamFilterReader
{

    /**
     * Defined definitions
     * @var PreProcessorContext
     */
    private $context = NULL;

    /**
     * Gets the context.
     * @return PreProcessorContext
     */
    public function getContext()
    {
        if ($this->context === NULL) {
            $this->context = new PreProcessorContext();
        }
        return $this->context;
    }

    /**
     * Reads input and returns pre-processed output.
     *
     * @return the resulting stream, or -1 if the end of the resulting stream has been reached
     *
     * @throws IOException if the underlying stream throws an IOException
     * during reading
     */
    function read($len = null)
    {

        if (!$this->getInitialized()) {
            $this->_initialize();
            $this->setInitialized(true);
        }

        $buffer = $this->in->read($len);
        if ($buffer === -1) {
            return -1;
        }
        $result = $this->process($buffer);
        return $result;
    }

    /**
     * Creates a new PreProcessorFilter using the passed in Reader for instantiation.
     *
     * @param reader A Reader object providing the underlying stream.
     * Must not be <code>null</code>.
     *
     * @return a new filter based on this configuration, but filtering
     * the specified reader
     */
    public function chain(Reader $reader)
    {
        $newFilter = new PreProcessorFilter($reader);
        $newFilter->setProject($this->getProject());
        return $newFilter;
    }

    /**
     * Initializes any parameters
     * This method is only called when this filter is used through a <filterreader> tag in build file.
     */
    private function _initialize()
    {
        $params = $this->getParameters();
        if ($params) {
            foreach ($params as $param) {
                if ($param->getName() == "macrodir") {
                    $this->getContext()->setMacroDir($param->getValue());
                } else {
                    $this->getContext()->addDefinition($param->getName(),
                                                       $param->getValue());
                }
            }
        }
    }

    /**
     * Pre-process a file
     * @param string $content File content to pre-process
     * @return string Pre-processed file
     */
    protected function process($content)
    {
        if (preg_match("/#(if|else|endif|call)/", $content) === 0) {
            // No directives found, do not treat file
            return $content;
        }

        $lines      = explode("\n", $content);
        $root       = new PreProcessorDirectiveRoot();
        $blockStack = array($root);
        $definitionsRegexp = "[A-Z-a-z0-9_]+";

        for ($i = 0; $i < sizeof($lines); $i++) {
            if (preg_match("/#if\s+($definitionsRegexp)/", $lines[$i], $matches) === 1) {
                // start new block
                $newBlock = new PreProcessorDirectiveIf($blockStack[0], $matches[1]);
                array_unshift($blockStack, $newBlock);  // New top
            } else if (preg_match("/#ifdef\s+($definitionsRegexp)/", $lines[$i],
                                  $matches) === 1) {
                $newBlock = new PreProcessorDirectiveIfdef($blockStack[0], $matches[1]);
                array_unshift($blockStack, $newBlock);  // New top
            } else if (preg_match("/#ifndef\s+($definitionsRegexp)/",
                                  $lines[$i], $matches) === 1) {
                $newBlock = new PreProcessor_Directive_Ifndef($blockStack[0], $matches[1]);
                array_unshift($blockStack, $newBlock);  // New top
            } else if (preg_match("/#elif\s+($definitionsRegexp)/", $lines[$i],
                                  $matches) === 1) {
                $newBlock = new PreProcessorDirectiveElif($blockStack[0], $matches[1]);
                array_unshift($blockStack, $newBlock);  // New top
            } else if (preg_match("/#elifdef\s+($definitionsRegexp)/",
                                  $lines[$i], $matches) === 1) {
                $newBlock = new PreProcessorDirectiveElifdef($blockStack[0], $matches[1]);
                array_unshift($blockStack, $newBlock);  // New top
            } else if (preg_match("/#elifndef\s+($definitionsRegexp)/",
                                  $lines[$i], $matches) === 1) {
                $newBlock = new PreProcessorDirectiveElifndef($blockStack[0], $matches[1]);
                array_unshift($blockStack, $newBlock);  // New top
            } else if (preg_match("/#else/", $lines[$i], $matches) === 1) {
                $newBlock = new PreProcessorDirectiveElse($blockStack[0]);
                array_unshift($blockStack, $newBlock);  // New top
            } else if (preg_match("/#endif/", $lines[$i], $matches) === 1) {
                do {
                    $shifted = array_shift($blockStack);
                } while ($shifted instanceof PreProcessorDirectiveElif
                || $shifted instanceof PreProcessorDirectiveElse);
            } else if (preg_match("/#call\s+($definitionsRegexp)\(([^)]*)\)/",
                                  $lines[$i], $matches) === 1) {
                $args     = explode(',', $matches[2]);
                array_walk($args, 'trim');
                $newBlock = new PreProcessorDirectiveCall(
                                $blockStack[0], $matches[1], $args
                );
            } else {
                $newBlock = new PreProcessorDirectiveCode($blockStack[0], $lines[$i]);
            }
        }

        $processedLines = $root->process($this->getContext());
        return implode("\n", $processedLines);
    }
}