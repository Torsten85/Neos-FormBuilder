<?php
namespace ByTorsten\FormBuilder\Finisher;

use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Http\Response;
use Neos\Flow\Mvc\Exception\StopActionException;
use Neos\Neos\Service\LinkingService;

class RedirectFinisher extends AbstractFinisher
{
    /**
     * @Flow\Inject
     * @var LinkingService
     */
    protected $linkingService;


    /**
     * @var NodeInterface
     */
    protected $target;

    /**
     * @param NodeInterface $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * @param NodeInterface $node
     * @throws StopActionException
     */
    protected function redirectToNode(NodeInterface $node)
    {
        $uri = $this->linkingService->createNodeUri(
            $this->controllerContext,
            $node
        );

        $statusCode = 303;
        /** @var Response $response */
        $response = $this->response;
        $response->setStatus($statusCode);
        $response->setHeader('Location', $uri);
        throw new StopActionException();
    }

    /**
     *
     */
    protected function validate()
    {
        if (! ($this->target instanceof NodeInterface)) {
            $this->addError('Target missing', 1493725122);
        }
    }

    /**
     * @param array $data
     */
    public function process(array $data)
    {
        if ($this->target) {
            $this->redirectToNode($this->target);
        }
    }
}