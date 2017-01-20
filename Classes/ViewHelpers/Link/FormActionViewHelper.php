<?php
namespace ByTorsten\FormBuilder\ViewHelpers\Link;

use Neos\Flow\Annotations as Flow;
use Neos\FluidAdaptor\ViewHelpers\Form\AbstractFormFieldViewHelper;

class FormActionViewHelper extends AbstractFormFieldViewHelper {

    /**
     * @var string
     */
    protected $tagName = 'button';

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerUniversalTagAttributes();
    }

    /**
     * @param string $action
     * @param array $arguments
     * @return string
     */
    public function render($action, $arguments = array()) {
        $this->tag->addAttribute('type', 'submit');
        $this->tag->setContent($this->renderChildren());
        $this->tag->forceClosingTag(TRUE);

        $this->tag->addAttribute('name', $this->prefixFieldName('_action'));

        $this->tag->addAttribute('value', $action . '#' . base64_encode(serialize($arguments)));

        return $this->tag->render();
    }
}