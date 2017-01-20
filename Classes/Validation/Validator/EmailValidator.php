<?php
namespace ByTorsten\FormBuilder\Validation\Validator;

class EmailValidator extends AbstractFormFieldValidator {

    /**
     * @param mixed $value
     * @return void
     */
    public function isValid($value) {
        $validator = $this->validatorResolver->createValidator('emailAddress');
        $this->result->merge($validator->validate($value));
    }
}