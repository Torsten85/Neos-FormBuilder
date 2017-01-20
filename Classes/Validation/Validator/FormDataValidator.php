<?php
namespace ByTorsten\FormBuilder\Validation\Validator;

use Neos\Flow\Validation\Validator\CollectionValidator;

use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Eel\Package as Eel;

class FormDataValidator extends CollectionValidator {

    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @var NodeInterface
     */
    protected $node;

    /**
     * @param NodeInterface $node
     * @return void
     */
    public function setNode($node)
    {
        $this->node = $node;
    }

    /**
     * @param $nodeType
     * @param NodeInterface $node
     * @return \Neos\Flow\Validation\Validator\ValidatorInterface
     */
    protected function getValidator($nodeType, NodeInterface $node)
    {
        $nodeConfiguration = $this->nodeTypeManager->getNodeType($nodeType);
        $validator = $nodeConfiguration->getConfiguration('form.validator');

        if (!$validator) {
            return $this->validatorResolver->createValidator(SimpleFormFieldValidator::class, ['node' => $node]);
        }

        if (preg_match(Eel::EelExpressionRecognizer, $validator)) {
            return $this->validatorResolver->createValidator(EelValidator::class, [
                'expression' => $validator,
                'node' => $node,
                'errorMessage' => $nodeConfiguration->getConfiguration('form.errorMessage')
            ]);
        }

        if (class_exists($validator)) {
            return $this->validatorResolver->createValidator($validator, ['node' => $node]);
        }

        return $this->validatorResolver->createValidator(SimpleFormFieldValidator::class, ['node' => $node]);
    }

    /**
    * @param array $data
    * @return void
    */
    public function isValid($data)
    {
        $query = new FlowQuery([$this->node]);
        $formNodes = $query->find('[instanceof ByTorsten.FormBuilder:FormElement]');

        /** @var NodeInterface $node */
        foreach($formNodes as $node) {
            $nodeIdentifier = $node->getIdentifier();
            $value = isset($data[$nodeIdentifier]) ? $data[$nodeIdentifier] : null;

            $validator = $this->getValidator($node->getNodeType()->getName(), $node);
            $this->result->forProperty($nodeIdentifier)->merge($validator->validate($value));
        }
    }
}
