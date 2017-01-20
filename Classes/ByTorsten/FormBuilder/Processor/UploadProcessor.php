<?php
namespace ByTorsten\FormBuilder\Processor;

use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\SwiftMailer\Message;
use Neos\ContentRepository\Domain\Model\Node;

class UploadProcessor extends AbstractProcessor {

    /**
     * @param Message $message
     * @param Resource $resource
     * @return string
     */
    protected function attachResource(Message $message, $resource) {
        if ($resource instanceof PersistentResource) {
            $attachment = \Swift_Attachment::fromPath('resource://' . $resource->getSha1());
            $attachment->setFilename($resource->getFilename());
            $attachment->setContentType($resource->getMediaType());
            $message->attach($attachment);

            return $resource->getFilename();
        }
    }

    /**
     * @param mixed $value
     * @param Message $message
     * @param Node $node
     * @return void
     */
    public function process(&$value, Message $message, Node $node) {

        if (is_array($value)) {
            $labels = [];
            foreach($value as $resource) {
                $currentLabel = $this->attachResource($message, $resource);
                if ($currentLabel) {
                    $labels[] = $currentLabel;
                }
            }
            $value = count($labels) > 0 ? implode("\n", $labels) : null;
        } else {
            $value = $this->attachResource($message, $value);
        }
    }
}