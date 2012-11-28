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

/**
 * Pre-processor filter
 * 
 * Usage:
 * <filterchain>
 * <filterreader classname="path.to.filters.PreProcessorFilter">
 * <param name="DEBUG" value="" />
 * </filterreader>
 * </filterchain>
 *
 * @author Thomas Muguet <t.muguet@thomasmuguet.info>
 */
class PreProcessorFilter extends BaseParamFilterReader
{

    /**
     * Defined definitions
     * @var array
     */
    public $definitions = array();

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
                $this->definitions[] = $param->getName();
            }
        }
    }

    /**
     * Evaluates a directive
     * @param array $directive
     * @return bool
     */
    private function evaluateDirective(array $directive)
    {
        switch ($directive[0]) {
            case 'ifdef':
                return in_array($directive[1], $this->definitions);

            case 'ifndef':
                return !in_array($directive[1], $this->definitions);

            case 'else':
                return !($this->evaluateContext($directive[1]));

            default:
                // unrecognized
                return FALSE;
        }
    }

    /**
     * Process a block and returnes lines of code
     * @param PreProcessorBlock $rootBlock Block to process
     * @return array
     */
    private function processBlock(PreProcessorBlock $rootBlock)
    {
        $lines = array();
        foreach ($rootBlock->subblocks as $block) {
            if ($block instanceof PreProcessorBlock) {
                if ($this->evaluateDirective($block->directive)) {
                    $lines = array_merge($lines, $this->processBlock($block));
                }
            } else {
                $lines[] = $block;
            }
        }
        return $lines;
    }

    /**
     * Pre-process a file
     * @param string $content File content to pre-process
     * @return string Pre-processed file
     */
    private function process($content)
    {
        $lines        = explode("\n", $content);
        $root         = new PreProcessorBlock();
        $currentBlock = $root;

        for ($i = 0; $i < sizeof($lines); $i++) {
            if (preg_match("/#(ifdef|ifndef) ([A-Z-a-z0-9_]+)/", $lines[$i],
                           $matches) === 1) {
                // start new block
                $newBlock            = new PreProcessorBlock();
                $newBlock->parent    = $currentBlock;
                $newBlock->directive = array($matches[1], $matches[2]);

                $currentBlock->subblocks[] = $newBlock;
                $currentBlock              = $newBlock;
            } else if (preg_match("/#else/", $lines[$i], $matches) === 1) {
                // end previous block
                $newBlock            = new PreProcessorBlock();
                $newBlock->parent    = $currentBlock->parent;
                $newBlock->directive = array("else", $currentBlock->directive);

                $currentBlock->parent->subblocks[] = $newBlock;
                $currentBlock                      = $newBlock;
            } else if (preg_match("/#endif/", $lines[$i], $matches) === 1) {
                $currentBlock = $currentBlock->parent;
            } else {
                $currentBlock->subblocks[] = $lines[$i];
            }
        }

        $processedLines = $this->processBlock($root);
        return implode("\n", $processedLines);
    }
}

/**
 * Internal structure for a block of code
 */
class PreProcessorBlock
{

    /**
     * Parent block
     * @var PreProcessorBlock
     */
    public $parent;

    /**
     * Child blocks
     * 
     * Instances of PreProcessorBlock or lines of code
     * @var array 
     */
    public $subblocks = array();

    /**
     * Pre-processor directive controlling this block
     * @var array 
     */
    public $directive = array();
}