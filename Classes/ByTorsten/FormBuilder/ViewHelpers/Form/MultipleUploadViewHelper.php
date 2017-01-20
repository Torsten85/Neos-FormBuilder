<?php
namespace ByTorsten\FormBuilder\ViewHelpers\Form;

use Neos\Flow\Annotations as Flow;
use Neos\FluidAdaptor\ViewHelpers\Form\UploadViewHelper;
use Neos\Flow\ResourceManagement\PersistentResource;

class MultipleUploadViewHelper extends UploadViewHelper  {

    /**
     * @return void
     * @api
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerTagAttribute('multiple', 'bool', 'Allows multiple files');
    }

    /**
     * @return array<\Neos\Flow\ResourceManagement\PersistentResource>
     */
    protected function getUploadedResources()
    {
        $resources = null;
        if ($this->hasMappingErrorOccurred()) {
            $resources = $this->getLastSubmittedFormData();
        } elseif ($this->hasArgument('value')) {
            $resources = $this->arguments['value'];
        } elseif ($this->isObjectAccessorMode()) {
            $resources = $this->getPropertyValue();
        }
        if ($resources === null) {
            return null;
        }
        if (
            is_array($resources) &&
            (count($resources) > 0) &&
            ($resources[0] instanceof PersistentResource)) {
            return $resources;
        }
        return $this->propertyMapper->convert($resources, 'array<\Neos\Flow\ResourceManagement\PersistentResource>');
    }

    /**
     * Renders the upload field.
     *
     * @return string
     * @api
     */
    public function render()
    {
        $nameAttribute = $this->getName();
        $this->registerFieldNameForFormTokenGeneration($nameAttribute);

        $output = '';
        $resources = $this->getUploadedResources();
        if ($resources !== null) {
            foreach($resources as $index => $resource) {
                $resourceIdentityAttribute = '';
                if ($this->hasArgument('id')) {
                    $resourceIdentityAttribute = ' id="' . htmlspecialchars($this->arguments['id']) . '-resource-identity-' . $index . '"';
                }
                $output .= '<input type="hidden" name="' . htmlspecialchars($nameAttribute) . '[' . $index . '][originallySubmittedResource][__identity]" value="' . $this->persistenceManager->getIdentifierByObject($resource) . '"' . $resourceIdentityAttribute . ' />';
            }
        }

        if ($this->hasArgument('collection') && $this->arguments['collection'] !== false && $this->arguments['collection'] !== '') {
            $output .= '<input type="hidden" name="'. htmlspecialchars($nameAttribute) . '[][__collectionName]" value="' . htmlspecialchars($this->arguments['collection']) . '" />';
        }

        $this->tag->addAttribute('type', 'file');
        $this->tag->addAttribute('name', $nameAttribute . '[]');

        $this->addAdditionalIdentityPropertiesIfNeeded();
        $this->setErrorClassAttribute();

        $output .= $this->tag->render();
        return $output;
    }
}