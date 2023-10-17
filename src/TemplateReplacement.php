<?php

namespace Classid\TemplateReplacement;

use Classid\TemplateReplacement\Abstracts\BaseTemplateReplacement;
use Classid\TemplateReplacement\Exceptions\InformationIsNotStringException;
use Classid\TemplateReplacement\Exceptions\InvalidBlueprintException;
use Illuminate\Support\Collection;
use ReflectionException;

class TemplateReplacement extends BaseTemplateReplacement
{
    /**
     * Description : use to build instance for static method
     * @return static
     */
    public static function build(): static
    {
        return new static();
    }


    /**
     * Description : use to change template placeholder into available data
     *
     * @param string $templatePattern
     * @param array $additionalMethodParams
     * @param array $priorityReplacementData
     * @return string
     */
    public static function execute(string $templatePattern, array $additionalMethodParams = [], array $priorityReplacementData = []): string
    {
        $instance = self::build();
        $refectionClass = new \ReflectionClass($instance);
        $instance->additionalMethodParams = $additionalMethodParams;


        foreach ($instance->getAllKeyThatNeedToReplace($templatePattern) as $key => $placeholder) {
            $valueToReplace = null;

            /**
             * first priority from param
             */
            if (isset($priorityReplacementData[$placeholder])) {
                $valueToReplace = $priorityReplacementData[$placeholder];
            } /**
             * method from overloading defined namespace
             * use _ to overload method from another class and override current class method
             */
            else if ($value = $instance->{"_" . self::getCamelCaseMethodNameFromSnakeCaseProperty($placeholder)}()) {
                $valueToReplace = $value;
            } /**
             * overload from another class to search method that does not exist on current class
             */
            else if ($value = $instance->{self::getCamelCaseMethodNameFromSnakeCaseProperty($placeholder)}()) {
                $valueToReplace = $value;
            } /**
             * overload property
             * use _ to overload property from another class and override current class property
             */
            else if ($value = $instance->{"_" . $placeholder}) {
                $valueToReplace = $value;

            } else if ($value = $instance->{$placeholder}) {
                $valueToReplace = $value;
            } /**
             * method or property from current class
             */
            else if ($refectionClass->hasMethod(self::getCamelCaseMethodNameFromSnakeCaseProperty($placeholder))) {
                $methodName = $refectionClass->getMethod(self::getCamelCaseMethodNameFromSnakeCaseProperty($placeholder))->name;
                $methodValue = $instance->{$methodName}();
                if (is_string($methodValue)) {
                    $valueToReplace = $methodValue;
                }
            } else if ($refectionClass->hasProperty($placeholder) && $refectionClass->getProperty($placeholder)->isInitialized($instance) && !is_null($refectionClass->getProperty($placeholder)->getValue($instance))) {
                $propertyValue = $refectionClass->getProperty($placeholder)->getValue($instance);
                if (is_string($propertyValue)) {
                    $valueToReplace = $propertyValue;
                }
            }


            if (!is_null($valueToReplace)) {
                $templatePattern = str_replace('{' . $placeholder . '}', $valueToReplace, $templatePattern);
            }
        }

        return $templatePattern;
    }

    /**
     * @param array $additionalMethodParams
     * @return Collection
     * @throws InformationIsNotStringException
     * @throws InvalidBlueprintException
     * @throws ReflectionException
     */
    public static function getAllData(array $additionalMethodParams = []): Collection
    {
        $instance = self::build();
        $instance->additionalMethodParams = $additionalMethodParams;
        $allData = collect([]);

        foreach ($instance->getAllAdditionalFile() as $file) {
            $additionalClassInstance = $instance->getAdditionalClassInstance($file);

            $reflectionClass = new \ReflectionClass($additionalClassInstance);
            foreach ($reflectionClass->getMethods() as $method) {
                if ($method->name === "__construct" || $method->name === "getParameter") {
                    continue;
                }

                $value = $method->invoke($additionalClassInstance);
                if (!is_string($value)) {
                    throw new InformationIsNotStringException("Data information of method name $method->name is not string");
                }
                $allData->put(self::getSnakeCaseFromCamelCaseMethodName($method->name), $value);
            }

            foreach ($reflectionClass->getProperties() as $property) {
                if ($property->name === "methodParams" || !$property->isInitialized($additionalClassInstance)) {
                    continue;
                }

                $value = $property->getValue($additionalClassInstance);
                if (!is_string($value)) {
                    throw new InformationIsNotStringException("Data information of method name $property->name is not string");
                }

                $allData->put($property->name, $property->getValue($additionalClassInstance));
            }
        }

        return $allData;
    }
}

