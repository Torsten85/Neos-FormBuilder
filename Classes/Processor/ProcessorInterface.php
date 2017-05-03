<?php
namespace ByTorsten\FormBuilder\Processor;

use Neos\ContentRepository\Domain\Model\NodeInterface;

interface ProcessorInterface {

    /**
     * @param string $value
     * @param NodeInterface $node
     * @return string
     */
    public function process($value, NodeInterface $node) : string;
}