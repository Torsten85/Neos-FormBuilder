<?php
namespace ByTorsten\FormBuilder\Processor;

use Neos\ContentRepository\Domain\Model\NodeInterface;

class MultiSelectProcessor implements ProcessorInterface {

    /**
     * @param mixed $value
     * @param NodeInterface $node
     * @return string
     */
    public function process($value, NodeInterface $node) : string {
        $options = $node->getProperty('options');

        $labels = [];
        foreach($value as $val) {
            $labels[] = $options[(integer) $val];
        }

        return implode("\n", $labels);
    }
}