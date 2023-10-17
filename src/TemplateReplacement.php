<?php

namespace Classid\TemplateReplacement;

use function PHPUnit\Framework\directoryExists;

class TemplateReplacement
{
    private array $allKeyThatNeedToReplace = [];

    /**
     * @return array
     */
    private function getAllAdditionalFile(): array
    {
        $dirPath = base_path(config("templatereplacement.additional_class_directory", "app/Services/GeneralReplacement"));
        if (!is_dir($dirPath)) {
            return [];
        }
        return array_values(array_filter(scandir($dirPath), function ($file) {
            return str_contains($file, '.php');
        }));
    }

    /**
     * @param string $filename
     * @return mixed|null
     */
    private function getAdditionalClassInstance(string $filename): ?object
    {
        $className = pathinfo($filename, PATHINFO_FILENAME);
        $fullClassName = config("templatereplacement.additional_class_namespace", "App\Services\GeneralReplacement") . "\\$className";
        if (class_exists($fullClassName)) {
            return new $fullClassName();
        }
        return null;
    }

    public function __call(string $name, array $arguments)
    {
        $name = str_replace("_", "", $name);

        foreach ($this->getAllAdditionalFile() as $file) {
            if ($instance = $this->getAdditionalClassInstance($file)) {
                if (method_exists($instance, $name)) {
                    return $instance->{$name}($arguments);
                }
            }
        }

        return null;
    }

    public function __get(string $name)
    {
        $name = str_replace("_", "", $name);
        foreach ($this->getAllAdditionalFile() as $file) {
            if ($instance = $this->getAdditionalClassInstance($file)) {
                if (property_exists($instance, $name)) {
                    return $instance->{$name};
                }
            }
        }
        return null;
    }

    private static function getInstance(): static
    {
        return new static();
    }

    private function getAllKeyThatNeedToReplace(string $templatePattern)
    {
        preg_match_all(config("templatereplacement.regex_pattern", '/\{(\w+)\}/'), $templatePattern, $this->allKeyThatNeedToReplace);
        return $this->allKeyThatNeedToReplace[1];
    }

    private static function getCamelCaseMethodNameFromSnakeCaseProperty(string $name): string
    {
        $methodName = str_replace("_", "", ucwords($name));
        return "get$methodName";
    }

    public static function execute(string $templatePattern, $priorityReplacementData = []): string
    {
        $instance = self::getInstance();
        $refectionClass = new \ReflectionClass($instance);

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
}
