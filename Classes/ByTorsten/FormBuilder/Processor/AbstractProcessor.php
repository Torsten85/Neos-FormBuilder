<?php
namespace ByTorsten\FormBuilder\Processor;

use Neos\SwiftMailer\Message;
use Neos\ContentRepository\Domain\Model\Node;

abstract class AbstractProcessor {

    /**
     * @param string $value
     * @param Message $message
     * @param Node $node
     * @return void
     */
    abstract public function process(&$value, Message $message, Node $node);
}