<?php
namespace ByTorsten\FormBuilder\Validation\Validator;
use Neos\Eel\CompilingEvaluator;
use Neos\Flow\Annotations as Flow;
use Neos\Eel\Utility as EelUtility;
use Neos\Eel\Helper as EelHelper;

class EelValidator extends AbstractFormFieldValidator {

    /**
     * @Flow\Inject
     * @var CompilingEvaluator
     */
    protected $compilingEvaluator;

    /**
     * @var array
     */
    protected $supportedOptions = array(
        'node' => array(null, 'The current node', '\Neos\ContentRepository\Domain\Model\Node', true),
        'expression' => array(null, 'The eel expression', 'string', true),
        'errorMessage' => array(null, 'Error message', 'string', false)
    );

    /**
     * @param mixed $value
     * @return void
     */
    public function isValid($value) {
        $result = EelUtility::evaluateEelExpression($this->options['expression'], $this->compilingEvaluator, [
            'value' =>  $value,
            'String' => new EelHelper\StringHelper(),
            'Array' => new EelHelper\ArrayHelper(),
            'Math' => new EelHelper\MathHelper()
        ]);

        if (!$result) {
            $message = $this->options['errorMessage'] ?: 'This property is invalid';
            $this->addError($message, 1281454673);
        }
    }

}