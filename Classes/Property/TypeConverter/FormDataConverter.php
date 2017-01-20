<?php
namespace ByTorsten\FormBuilder\Property\TypeConverter;

use Neos\Flow\Property\TypeConverter\AbstractTypeConverter;
use Neos\ContentRepository\Domain\Model\Node;
use Neos\Flow\Property\PropertyMappingConfigurationInterface;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;

use Neos\FLow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class FormDataConverter extends AbstractTypeConverter  {

    /**
     * @var integer
     */
    protected $priority = -10;

    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @param mixed $source
     * @return array<string>
     */
    public function getSourceChildPropertiesToBeConverted($source)
    {
        return $source;
    }


    /**
     * @param string $targetType
     * @param string $propertyName
     * @param PropertyMappingConfigurationInterface $configuration
     * @return string the type of $propertyName in $targetType
     */
    public function getTypeOfChildProperty($targetType, $propertyName, PropertyMappingConfigurationInterface $configuration)
    {
        /** @var Node $node */
        $node = $configuration->getConfigurationValue(FormDataConverter::class, 'node');
        $query = new FlowQuery([$node]);

        /** @var Node $targetNode */
        $targetNode = $query->find('#' . $propertyName)->get(0);
        $type = $this->nodeTypeManager->getNodeType($targetNode->getNodeType()->getName())->getConfiguration('form.targetType');

        return $type;
    }

    /**
     * @param mixed $source
     * @param string $targetType
     * @param array $convertedChildProperties
     * @param \Neos\Flow\Property\PropertyMappingConfigurationInterface $configuration
     * @return mixed|\Neos\Flow\Error\Error
     * @throws \Neos\Flow\Property\Exception\TypeConverterException
     */
    public function convertFrom($source, $targetType, array $convertedChildProperties = array(), PropertyMappingConfigurationInterface $configuration = null)
    {
        return $convertedChildProperties;
    }
}