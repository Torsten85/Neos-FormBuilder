<?php
namespace ByTorsten\FormBuilder\ViewHelpers\Form;

use Neos\FluidAdaptor\ViewHelpers\Form\AbstractFormFieldViewHelper;
use Neos\Flow\Property\PropertyMapper;

use Neos\Flow\Annotations as Flow;

class LastSubmittedFormDataViewHelper extends AbstractFormFieldViewHelper {

    /**
     * @Flow\Inject
     * @var PropertyMapper
     */
    protected $propertyMapper;

    /**
     * @param string $as
     * @param string $targetType
     * @return mixed
     */
    public function render($as = 'value', $targetType = 'string') {
        $data = $this->getLastSubmittedFormData();

        if ($targetType[0] === '\\') {
            $targetType = substr($targetType, 1);
        }

        if ($data !== NULL) {
            $data = $this->propertyMapper->convert($data, $targetType);
        }

        $this->templateVariableContainer->add($as, $data);
        $output = $this->renderChildren();
        $this->templateVariableContainer->remove($as);

        return $output;
    }
}