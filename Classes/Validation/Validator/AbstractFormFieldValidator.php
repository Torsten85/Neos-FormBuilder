<?php
namespace ByTorsten\FormBuilder\Validation\Validator;

use Neos\Error\Messages\Result;
use Neos\Flow\Validation\Validator\AbstractValidator;
use Neos\ContentRepository\Domain\Model\Node;

use Neos\Flow\Annotations as Flow;

abstract class AbstractFormFieldValidator extends AbstractValidator {

    /**
     * @var \Neos\Flow\Validation\ValidatorResolver
     * @Flow\Inject
     */
    protected $validatorResolver;

    /**
     * @var array
     */
    protected $supportedOptions = array(
        'node' => array(null, 'The current node', '\Neos\ContentRepository\Domain\Model\Node', true)
    );

    /**
     * @var Node
     */
    protected $node;

    /**
     * @return void
     */
    public function initializeObject() {

        /** @var Node node */
        $this->node = $this->options['node'];

        $this->acceptsEmptyValues = $this->node->getProperty('required') === FALSE;
    }

    /**
     * @param mixed $value
     * @return Result
     */
    public function validate($value) {
        $this->result = new Result();

        if ($this->node->getProperty('required')) {
            $validator = $this->validatorResolver->createValidator('notEmpty');
            $this->result->merge($validator->validate($value));
        }

        if (!$this->result->hasErrors()) {
            $this->isValid($value);
        }

        return $this->result;
    }
}