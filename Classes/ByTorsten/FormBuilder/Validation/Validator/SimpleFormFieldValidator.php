<?php
namespace ByTorsten\FormBuilder\Validation\Validator;

class SimpleFormFieldValidator extends AbstractFormFieldValidator {

    /**
     * @param mixed $value
     * @return void
     */
    public function isValid($value) {
        // Do nothing. Not empty check is already done
    }
}