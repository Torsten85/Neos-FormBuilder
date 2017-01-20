<?php
namespace ByTorsten\FormBuilder\Controller;

use ByTorsten\NeosPluginBase\Controller\AbstractPluginController;
use Neos\Flow\Mvc\Exception\InvalidActionVisibilityException;
use Neos\Flow\Mvc\Exception\NoSuchActionException;
use Neos\Utility\ObjectAccess;

abstract class AbstractFormController extends AbstractPluginController {

    /**
     * @return string
     * @throws InvalidActionVisibilityException
     * @throws NoSuchActionException
     */
    protected function resolveActionMethodName()
    {
        if ($this->request->hasArgument('_action')) {
            list($action, ) = explode('#', $this->request->getArgument('_action'));

            $actionMethodName = $action . 'Action';
            if (!is_callable(array($this, $actionMethodName))) {
                throw new NoSuchActionException(sprintf('An action "%s" does not exist in controller "%s".', $actionMethodName, get_class($this)), 1186669086);
            }

            $publicActionMethods = static::getPublicActionMethods($this->objectManager);
            if (!isset($publicActionMethods[$actionMethodName])) {
                throw new InvalidActionVisibilityException(sprintf('The action "%s" in controller "%s" is not public!', $actionMethodName, get_class($this)), 1186669086);
            }
            return $actionMethodName;
        }


        return parent::resolveActionMethodName();
    }

    /**
     * @return void
     */
    protected function mapRequestArgumentsToControllerArguments()
    {
        if ($this->request->hasArgument('_action')) {

            list(, $rawArguments) = explode('#', $this->request->getArgument('_action'));
            $arguments = unserialize(base64_decode($rawArguments));
            foreach($arguments as $argumentName => $argumentValue) {
                $this->request->setArgument($argumentName, $argumentValue);
            }
        }

        parent::mapRequestArgumentsToControllerArguments();
    }

    /**
     * @return void
     */
    protected function forwardToForm() {
        $referringRequest = $this->request->getReferringRequest();

        if ($referringRequest === null) {
            return;
        }
        $packageKey = $referringRequest->getControllerPackageKey();
        $subpackageKey = $referringRequest->getControllerSubpackageKey();
        if ($subpackageKey !== null) {
            $packageKey .= '\\' . $subpackageKey;
        }
        $argumentsForNextController = $referringRequest->getArguments();
        $argumentsForNextController['__submittedArguments'] = $this->request->getArguments();
        $validationResults = $this->arguments->getValidationResults();

        ObjectAccess::setProperty($validationResults, 'errorsExist', true, true);

        $argumentsForNextController['__submittedArgumentValidationResults'] = $validationResults;

        $this->forward($referringRequest->getControllerActionName(), $referringRequest->getControllerName(), $packageKey, $argumentsForNextController);
    }
}