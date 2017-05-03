<?php
namespace ByTorsten\FormBuilder\Finisher;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Error\Messages\Result;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Flow\Mvc\RequestInterface;
use Neos\Flow\Mvc\ResponseInterface;
use Neos\Flow\Validation\Error as ValidationError;

abstract class AbstractFinisher implements FinisherInterface
{
    /**
     * @var NodeInterface
     */
    protected $node;

    /**
     * @var ControllerContext
     */
    protected $controllerContext;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var bool
     */
    protected $enabled = false;

    /**
     * @var Result
     */
    protected $result;

    /**
     * AbstractFinisher constructor.
     * @param NodeInterface $node
     * @param ControllerContext $controllerContext
     */
    public function __construct(NodeInterface $node, ControllerContext $controllerContext)
    {
        $this->node = $node;
        $this->controllerContext = $controllerContext;
        $this->request = $controllerContext->getRequest();
        $this->response = $controllerContext->getResponse();
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (bool) $enabled;
    }

    /**
     * @param string $message
     * @param integer $code
     * @param array $arguments
     * @return void
     * @api
     */
    protected function addError($message, $code, array $arguments = [])
    {
        $this->result->addError(new ValidationError($message, $code, $arguments));
    }

    /**
     * @return mixed
     */
    abstract protected function validate();

    /**
     * @return Result
     */
    public function getValidationErrors(): Result
    {
        $this->result = new Result();
        $this->validate();
        return $this->result;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
