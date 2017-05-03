<?php
namespace ByTorsten\FormBuilder\Finisher;

use Neos\Error\Messages\Result;

interface FinisherInterface
{
    /**
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * @param array $data
     */
    public function process(array $data);

    /**
     * @return Result
     */
    public function getValidationErrors(): Result;
}