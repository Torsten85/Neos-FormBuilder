<?php
namespace ByTorsten\FormBuilder\Processor;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\SwiftMailer\Message;

class UploadProcessor implements ProcessorInterface {

    /**
     * @param mixed $value
     * @param NodeInterface $node
     * @return string
     */
    public function process($value, NodeInterface $node) : string {

        if (is_array($value)) {
            $labels = [];
            foreach($value as $resource) {
                if ($resource instanceof PersistentResource) {
                    $labels[] = $resource->getFilename();
                }
            }
            return count($labels) > 0 ? implode("\n", $labels) : '';
        }

        if ($value instanceof PersistentResource) {
            return $value->getFilename();
        }

        return '';
    }
}