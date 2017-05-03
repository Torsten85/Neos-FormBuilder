<?php
namespace ByTorsten\FormBuilder\Service;

use Neos\Flow\Annotations as Flow;

use ByTorsten\FormBuilder\Finisher\FinisherInterface;
use ByTorsten\FormBuilder\Processor\ProcessorInterface;
use ByTorsten\FormBuilder\Exception as FormBuilderException;
use Neos\Eel\CompilingEvaluator;
use Neos\Error\Messages\Error;
use Neos\Error\Messages\Result;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Eel\Package as Eel;
use Neos\Eel\Utility as EelUtility;
use Neos\Eel\Helper as EelHelper;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Utility\ObjectAccess;
use Neos\Utility\PositionalArraySorter;

class FinisherService
{

    const FORMBUILDER_NODE_TYPE = 'ByTorsten.FormBuilder:Plugin';
    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @Flow\Inject
     * @var CompilingEvaluator
     */
    protected $compilingEvaluator;

    /**
     * @var array
     */
    protected $finishers;

    /**
     * @Flow\InjectConfiguration("finishers")
     * @var array
     */
    protected $finisherSettings;

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
     * @param NodeInterface $node
     * @param array $data
     * @return array
     * @throws FormBuilderException
     */
    protected function prepareData(NodeInterface $node, array $data)
    {
        $processedData = [];
        $query = new FlowQuery([$node]);

        foreach($data as $nodeIdentifier => $value) {
            /** @var NodeInterface $node */
            $node = $query->find('#' . $nodeIdentifier)->get(0);
            $nodeTypeName = $node->getNodeType()->getName();
            $formConfiguration = $this->nodeTypeManager->getNodeType($nodeTypeName)->getConfiguration('options.form');

            $label = $node->getProperty('internalLabel') ?: $node->getProperty('label') ?: $node->getProperty('placeholder');
            $label = strip_tags($label);

            if (isset($formConfiguration['processor'])) {
                $processor = $formConfiguration['processor'];
                if (class_exists($processor)) {

                    $processorImplementation = new $processor();
                    if ($processorImplementation instanceof ProcessorInterface) {
                        $processedValue = $processorImplementation->process($value, $node);
                    } else {
                        throw new FormBuilderException(sprintf('Processor class %s has to implement ProcessorInterface.', $processor), 103215326);
                    }
                } else {
                    throw new FormBuilderException(sprintf('Processor class %s does not exist.', $processor), 103215325);
                }
            } else {
                $processedValue = (string) $value;
            }

            if (isset($formConfiguration['value'])) {
                $valueConfiguration = $formConfiguration['value'];

                if (preg_match(Eel::EelExpressionRecognizer, $valueConfiguration)) {
                    $processedValue = $this->evaluate($valueConfiguration, [
                        'node' => $node,
                        'value' => $value
                    ]);
                }
            }

            $processedData[$label] = [
                'originalValue' => $value,
                'processedValue' => $processedValue
            ];
        }

        return $processedData;
    }

    /**
     * @param NodeInterface $node
     * @param ControllerContext $controllerContext
     * @return array
     * @throws FormBuilderException
     */
    protected function getFinishers(NodeInterface $node, ControllerContext $controllerContext): array
    {
        if ($this->finishers) {
            return $this->finishers;
        }

        $sorter = new PositionalArraySorter($this->finisherSettings);
        $finisherConfiguration = $sorter->toArray();
        $finishers = [];

        foreach($finisherConfiguration as $key => $confguration) {
            $finisherClassName = $confguration['class'];

            if (!class_exists($finisherClassName)) {
                throw new FormBuilderException(sprintf('Finisher class %s not found', $finisherClassName));
            }

            $finisher = new $finisherClassName($node, $controllerContext);

            if (! ($finisher instanceof FinisherInterface)) {
                throw new FormBuilderException(sprintf('Finisher $s has to implement FinisherInterface', $finisherClassName));
            }
            $finishers[$key] = $finisher;
        }

        $properties = $this->nodeTypeManager->getNodeType(self::FORMBUILDER_NODE_TYPE)->getConfiguration('properties');

        foreach($properties as $propertyName => $propertyConfig) {
            $finisher = $propertyConfig['options']['finisher'] ?? null;
            if ($finisher) {
                $paths = is_array($finisher) ? $finisher : [$finisher];

                foreach($paths as $path) {
                    list($finisherIdentifier, $finisherProperty) = explode('.', $path);
                    if (!isset($finishers[$finisherIdentifier])) {
                        throw new FormBuilderException(sprintf('No finisher %s found in property %s', $finisherIdentifier, $propertyName));
                    }

                    $isSet = ObjectAccess::setProperty($finishers[$finisherIdentifier], $finisherProperty, $node->getProperty($propertyName));
                    if (!$isSet) {
                        throw new FormBuilderException(sprintf('Could not set property %s for finisher %s', $finisherProperty, $finisherIdentifier));
                    }
                }
            }
        }
        $this->finishers = $finishers;
        return $finishers;
    }

    /**
     * @param NodeInterface $node
     * @param ControllerContext $controllerContext
     * @param array $formData
     */
    public function finish(NodeInterface $node, ControllerContext $controllerContext, array $formData)
    {
        $processedData = $this->prepareData($node, $formData);
        $finishers = $this->getFinishers($node, $controllerContext);

        /** @var FinisherInterface $finisher */
        foreach($finishers as $finisher) {
            if ($finisher->isEnabled() && !$finisher->getValidationErrors()->hasErrors()) {
                $finisher->process($processedData);
            }
        }
    }

    /**
     * @param NodeInterface $node
     * @param ControllerContext $controllerContext
     * @return Result
     */
    public function getConfigurationErrors(NodeInterface $node, ControllerContext $controllerContext)
    {
        $finishers = $this->getFinishers($node, $controllerContext);

        $errors = new Result();
        $hasActiveFinishers = false;
        /** @var FinisherInterface $finisher */
        foreach($finishers as $key => $finisher) {
            if ($finisher->isEnabled()) {
                $hasActiveFinishers = true;
                $errors->forProperty($key)->merge($finisher->getValidationErrors());
            }
        }

        if (!$hasActiveFinishers) {
            $errors->addError(new Error('No active finishers', 1493724929));
        }

        return $errors;
    }
}