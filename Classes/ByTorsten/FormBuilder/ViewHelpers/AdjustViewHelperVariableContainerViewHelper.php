<?php
namespace ByTorsten\FormBuilder\ViewHelpers;

use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;
use Neos\FluidAdaptor\ViewHelpers\FormViewHelper;

class AdjustViewHelperVariableContainerViewHelper extends AbstractViewHelper {

    /**
     * @var boolean
     */
    protected $escapeChildren = false;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @param string $objectName
     * @return string
     */
    public function render($objectName) {
        $fieldNamePrefix = $this->controllerContext->getRequest()->getArgumentNamespace();

        $this->viewHelperVariableContainer->add(FormViewHelper::class, 'formObjectName', $objectName);
        $this->viewHelperVariableContainer->add(FormViewHelper::class, 'fieldNamePrefix', $fieldNamePrefix);
        $content = $this->renderChildren();
        $this->viewHelperVariableContainer->remove(FormViewHelper::class, 'fieldNamePrefix');
        $this->viewHelperVariableContainer->remove(FormViewHelper::class, 'formObjectName');

        return $content;
    }
}