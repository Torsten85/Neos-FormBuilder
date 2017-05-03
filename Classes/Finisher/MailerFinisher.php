<?php
namespace ByTorsten\FormBuilder\Finisher;

use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\FluidAdaptor\View\StandaloneView;
use Neos\SwiftMailer\Message;

class MailerFinisher extends AbstractFinisher
{
    /**
     * @var string
     */
    protected $recipient;

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var string
     */
    protected $template;

    /**
     * @param string $recipient
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @param PersistentResource $resource
     * @param Message $message
     */
    protected function attachResource(PersistentResource $resource, Message $message)
    {
        $attachment = \Swift_Attachment::fromPath('resource://' . $resource->getSha1());
        $attachment->setFilename($resource->getFilename());
        $attachment->setContentType($resource->getMediaType());
        $message->attach($attachment);
    }

    /**
     *
     */
    protected function validate()
    {
        if (!$this->recipient) {
            $this->addError('Recipient missing', 1493724926);
        }

        if (!$this->subject) {
            $this->addError('Subject missing', 1493724927);
        }

        if (!$this->template) {
            $this->addError('Template missing', 1493724928);
        }
    }

    /**
     * @param array $data
     */
    public function process(array $data)
    {
        $view = new StandaloneView($this->request);
        $view->setTemplateSource($this->template);

        $templateData = array_combine(array_keys($data), array_map(function ($valueSet) {
            return $valueSet['processedValue'];
        }, $data));

        $view->assign('data', $templateData);

        $message = new Message();
        $message->setTo($this->recipient);
        $message->setSubject($this->subject);
        $message->setBody($view->render(), 'text/plain');

        foreach($data as $key => $valueSet) {
            if ($valueSet['originalValue'] instanceof PersistentResource) {
                $this->attachResource($valueSet['originalValue'], $message);
            } else if (is_array($valueSet['originalValue'])) {
                foreach($valueSet['originalValue'] as $value) {
                    if ($value instanceof PersistentResource) {
                        $this->attachResource($value, $message);
                    }
                }
            }
        }

        $message->send();
    }
}