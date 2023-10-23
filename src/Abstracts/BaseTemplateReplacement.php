<?php

namespace Classid\TemplateReplacement\Abstracts;

use Classid\TemplateReplacement\Exceptions\InformationIsNotStringException;
use Classid\TemplateReplacement\Exceptions\InvalidBlueprintException;
use Classid\TemplateReplacement\Interfaces\InformationInterface;

abstract class BaseTemplateReplacement
{
    protected const REGEX_PATTERN = '/\{(\w+)\}/';
    protected array $allKeyThatNeedToReplace = [];
    protected array $additionalMethodParams = [];

    /**
     * Description : use to get method from another available class
     * @param string $name
     * @param array $arguments
     * @return null
     * @throws InvalidBlueprintException
     * @throws InformationIsNotStringException
     */
    public function __call(string $name, array $arguments)
    {
        $name = str_replace("_", "", $name);
        foreach ($this->getAllAdditionalFile() as $file) {
            $instance = $this->getAdditionalClassInstance($file);
            if ($instance && method_exists($instance, $name)) {
                if (!is_string($instance->{$name}($arguments))){
                    throw new InformationIsNotStringException("Data information of method name $name is not string");
                }
                return $instance->{$name}($arguments);
            }
        }

        return null;
    }

    /**
     * Description : use to get property from another available class
     *
     * @param string $name
     * @return null
     * @throws InvalidBlueprintException
     * @throws InformationIsNotStringException
     */
    public function __get(string $name)
    {
        $name = str_replace("_", "", $name);
        foreach ($this->getAllAdditionalFile() as $file) {
            $instance = $this->getAdditionalClassInstance($file);
            if ($instance && property_exists($instance, $name)) {
                if (!is_string($instance->{$name})){
                    throw new InformationIsNotStringException("Data information of property name $name is not string");
                }
                return $instance->{$name};
            }
        }
        return null;
    }


    /**
     * Description : transform to snake case from camel case string
     *  ex: transfer full_name into getFullName
     * @param string $methodName
     * @return string
     */
    protected static function getSnakeCaseFromCamelCaseMethodName(string $methodName): string
    {
        if (str_starts_with($methodName, 'get')) {
            $methodName = substr($methodName, 3);
        }

        $result = preg_replace('/[A-Z]/', '_$0', $methodName);
        $result = strtolower($result);

        if (str_starts_with($result, '_')) {
            $result = substr($result, 1);
        }

        return $result;
    }

    /**
     * Description : transform to camel case from snake case string
     * ex: transfer full_name into getFullName
     * @param string $name
     * @return string
     */
    protected static function getCamelCaseMethodNameFromSnakeCaseProperty(string $name): string
    {
        $methodName = str_replace("_", "", ucwords($name));
        return "get$methodName";
    }

    /**
     * Description: get all data placeholder key from string pattern by regex
     * default regex {}
     * so when string pattern contain {name}, {target}
     * it will return [name, target]
     *
     * @param string $templatePattern
     * @return array
     */
    protected function getAllKeyThatNeedToReplace(string $templatePattern): array
    {
        preg_match_all(self::REGEX_PATTERN, $templatePattern, $this->allKeyThatNeedToReplace);
        return $this->allKeyThatNeedToReplace[1];
    }

    /**
     * Description : use to get all additional file for custom information from defined directory
     * @return array
     */
    protected function getAllAdditionalFile(): array
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
     * Description : this will return back instance of additional class for specified file
     * @param string $filename
     * @return mixed|null
     * @throws InvalidBlueprintException
     */
    protected function getAdditionalClassInstance(string $filename): ?object
    {
        $className = pathinfo($filename, PATHINFO_FILENAME);
        $fullClassName = config("templatereplacement.additional_class_namespace", "App\Services\GeneralReplacement") . "\\$className";
        if (class_exists($fullClassName)) {
            $instance = new $fullClassName($this->additionalMethodParams);
            if (!$instance instanceof InformationInterface) {
                throw new InvalidBlueprintException("Invalid class interface. Class $fullClassName should implement Classid\TemplateReplacement\Interfaces\InformationInterface");
            }

            return $instance;
        }
        return null;
    }
}
