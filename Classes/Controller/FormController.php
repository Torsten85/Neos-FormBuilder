<?php
namespace ByTorsten\FormBuilder\Controller;

use ByTorsten\FormBuilder\Property\TypeConverter\FormDataConverter;
use ByTorsten\FormBuilder\Service\FinisherService;
use Neos\Flow\Property\PropertyMapper;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Flow\Validation\Validator\ConjunctionValidator;
use ByTorsten\FormBuilder\Validation\Validator\FormDataValidator;

use Neos\Flow\Annotations as Flow;

class FormController extends AbstractFormController
{
    /**
     * @Flow\Inject
     * @var PropertyMapper
     */
    protected $propertyMapper;

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @Flow\Inject
     * @var FinisherService
     */
    protected $finisherService;

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->view->assign('node', $this->node);
        $this->view->assign('submitButtonLabel', $this->tsValue('submitButtonLabel'));

        if ($this->node->getContext()->isInBackend()) {
            $configurationErrors = $this->finisherService->getConfigurationErrors($this->node, $this->controllerContext);
            $this->view->assign('configurationErrors', $configurationErrors);
        }
    }

    /**
     * @param string $property
     * @param int $index
     */
    public function clearUploadAction($property, $index = null) {
        /** @var array $data */
        $data = $this->request->getArgument('data');

        if (isset($data[$property]) && ($index === null || isset($data[$property][$index]))) {
            $value = $data[$property];

            if ($index !== null) {
                $value = $value[$index];
            }

            $resource = $this->propertyMapper->convert($value, PersistentResource::class);
            $this->resourceManager->deleteResource($resource);

            if ($index !== null) {
                array_splice($data[$property], $index, 1);
            } else {
                unset($data[$property]);
            }

            $this->request->setArgument('data', $data);
        }

        $this->forwardToForm();
    }

    /**
     * @return void
     */
    public function initializeSubmitAction()
    {
        /* @var ConjunctionValidator $conjunctionValidator */
        $conjunctionValidator = $this->arguments->getArgument('data')->getValidator();
        $validators = $conjunctionValidator->getValidators();
        $validators->rewind();
        /* @var FormDataValidator $validator */
        $validator = $validators->current();
        $validator->setNode($this->node);


        $configuration = $this->arguments->getArgument('data')->getPropertyMappingConfiguration();
        $configuration->allowAllProperties();
        $configuration->forProperty('*')->allowAllProperties();
        $configuration->setTypeConverter(new FormDataConverter());
        $configuration->setTypeConverterOption(FormDataConverter::class, 'node', $this->node);
    }

    /**
     * @param array $data
     * @Flow\Validate(argumentName="data", type="\ByTorsten\FormBuilder\Validation\Validator\FormDataValidator")
     * @return void
     */
    public function submitAction($data)
    {
        $this->finisherService->finish($this->node, $this->controllerContext, $data);
    }

    /**
     * @return bool
     */
    protected function getErrorFlashMessage()
    {
        return false;
    }
}
