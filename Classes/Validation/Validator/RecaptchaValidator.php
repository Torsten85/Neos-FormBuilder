<?php
namespace ByTorsten\FormBuilder\Validation\Validator;

use ReCaptcha\ReCaptcha;
use Neos\Flow\Annotations as Flow;

class RecaptchaValidator extends AbstractFormFieldValidator {

    /**
     * @var string
     * @Flow\InjectConfiguration("recaptcha.secretKey")
     */
    protected $configuredSecret;

    /**
     * @param mixed $value
     * @return void
     */
    public function isValid($value)
    {
        $node = $this->node;
        $nodeSecret = $node->getProperty('secretkey');

        $secretKey = $nodeSecret ?: $this->configuredSecret;
        $recaptcha = new ReCaptcha($secretKey);
        $response = $recaptcha->verify($value);

        if (!$response->isSuccess()) {
            $this->addError('You did not pass the recatpcha verification', 1474097993);
        }
    }
}