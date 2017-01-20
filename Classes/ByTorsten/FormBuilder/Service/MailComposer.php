<?php
namespace ByTorsten\FormBuilder\Service;

use ByTorsten\FormBuilder\Processor\AbstractProcessor;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\SwiftMailer\Message;
use Neos\ContentRepository\Domain\Model\Node;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Service\NodeServiceInterface;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\Eel\Utility as EelUtility;
use Neos\Eel\Helper as EelHelper;
use Neos\Eel\Package as Eel;
use Neos\Eel\CompilingEvaluator;

use Neos\Flow\Annotations as Flow;

class MailComposer {

    /**
     * @var NodeServiceInterface
     */
    protected $node;

    /**
     * @var array
     */
    protected $data;

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
    protected $header;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var string
     */
    protected $footer;

    /**
     * @Flow\Inject
     * @var CompilingEvaluator
     */
    protected $compilingEvaluator;

    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @Flow\InjectConfiguration("mail.sender")
     * @var string
     */
    protected $sender;

    /**
     * @param string $recipient
     * @return void
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;
    }

    /**
     * @param string $subject
     * @return void
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @param $header
     * @param $content
     * @param $footer
     * @throws MailComposerException
     */
    public function setMailParts($header, $content, $footer)
    {
        if (strpos($content, '{label}') === FALSE) {
            throw new MailComposerException('Your missing the {label} placeholder within your content template', 1384192951);
        }

        if (strpos($content, '{value}') === FALSE) {
            throw new MailComposerException('Your missing the {value} placeholder within your content template', 1384192952);
        }

        $this->header = $header;
        $this->content = $content;
        $this->footer = $footer;
    }

    /**
     * @param NodeInterface $node
     * @return void
     */
    public function setNode($node)
    {
        $this->node = $node;
    }

    /**
     * @param array $data
     * @return void
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @param string $expression
     * @param array $arguments
     * @return string
     */
    protected function evaluate($expression, array $arguments) {
        return EelUtility::evaluateEelExpression($expression, $this->compilingEvaluator, array_merge($arguments, [
            'String' => new EelHelper\StringHelper(),
            'Array' => new EelHelper\ArrayHelper(),
            'Math' => new EelHelper\MathHelper()
        ]));
    }

    /**
     * @param Message $message
     * @return array
     * @throws \Exception
     */
    protected function composeBody($message)
    {
        $content = [];
        $query = new FlowQuery([$this->node]);
        $email = null;
        foreach($this->data as $nodeIdentifier => $value) {
            /** @var Node $node */
            $node = $query->find('#' . $nodeIdentifier)->get(0);

            $nodeTypeName = $node->getNodeType()->getName();


            $formConfiguration = $this->nodeTypeManager->getNodeType($nodeTypeName)->getConfiguration('form');

            $label = $node->getProperty('internalLabel') ?: $node->getProperty('label') ?: $node->getProperty('placeholder');
            $label = strip_tags($label);

            if (isset($formConfiguration['processor'])) {
                $processor = $formConfiguration['processor'];
                if (class_exists($processor)) {

                    $processorImplementation = new $processor();
                    if ($processorImplementation instanceof AbstractProcessor) {
                        $processorImplementation->process($value, $message, $node);
                    } else {
                        throw new \Exception('Processor class ' . $processor . ' has to extend AbstractProcessor.', 103215326);
                    }
                } else {
                    throw new \Exception('Processor class ' . $processor . ' does not exist.', 103215325);
                }
            }

            if (isset($formConfiguration['value'])) {
                $valueConfiguration = $formConfiguration['value'];

                if (preg_match(Eel::EelExpressionRecognizer, $valueConfiguration)) {
                    $value = $this->evaluate($valueConfiguration, [
                        'node' => $node,
                        'value' => $value
                    ]);
                }
            }

            if ($nodeTypeName === 'ByTorsten.FormBuilder:Email') {
                $email = $value;
            }

            if ($value) {
                $content[] = str_replace(['{label}', '{value}'], [$label, $value], $this->content);
            }
        }

        $body = sprintf("%s\n\n%s\n\n%s", $this->header, trim(implode("\n", $content)), $this->footer);
        return [$email, $body];
    }

    /**
     *
     */
    public function dispatch() {
        $message = new Message();
        $message->addTo($this->recipient);
        $message->setFrom($this->sender);
        $message->setSubject($this->subject);

        list ($senderMail, $body) = $this->composeBody($message);

        if ($senderMail) {
            $message->setReplyTo($senderMail);
        }

        $message->setBody($body, 'text/plain');
        $message->send();
    }
}