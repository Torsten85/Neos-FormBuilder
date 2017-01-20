<?php
namespace ByTorsten\FormBuilder\Processor;

use Neos\SwiftMailer\Message;
use Neos\ContentRepository\Domain\Model\Node;

class MultiSelectProcessor extends AbstractProcessor {

    /**
     * @param mixed $value
     * @param Message $message
     * @param Node $node
     * @return void
     */
    public function process(&$value, Message $message, Node $node) {
        $options = $node->getProperty('options');

        $labels = [];
        foreach($value as $val) {
            $labels[] = $options[(integer) $val];
        }

        $value = implode("\n", $labels);
    }
}