<?php

namespace Classid\TemplateReplacement\Abstracts;

use Classid\TemplateReplacement\Exceptions\InvalidBlueprintException;
use Classid\TemplateReplacement\Interfaces\InformationInterface;

class BaseTemplateReplacement
{
    protected const REGEX_PATTERN = '/\{(\w+)\}/';
    protected array $allKeyThatNeedToReplace = [];
    protected array $additionalMethodParams = [];

    /**
     * Description : use to get method from another available class
     * @param string $name
     * @param array $arguments
     * @return null
     */
    public function __call(string $name, array $arguments)
    {
        $name = str_replace("_", "", $name);
        foreach ($this->getAllAdditionalFile() as $file) {
            $instance = $this->getAdditionalClassInstance($file);
            if ($instance && method_exists($instance, $name)) {
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
     */
    public function __get(string $name)
    {
        $name = str_replace("_", "", $name);
        foreach ($this->getAllAdditionalFile() as $file) {
            $instance = $this->getAdditionalClassInstance($file);
            if ($instance && property_exists($instance, $name)) {
                return $instance->{$name};
            }
        }
        return null;
    }


    /**
     * Description : transform snake case from camel case string
     * ex: transfer full_name into getFullName
     * @param string $name
     * @return string
     */
    public static function getCamelCaseMethodNameFromSnakeCaseProperty(string $name): string
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
    public function getAllKeyThatNeedToReplace(string $templatePattern): array
    {
        preg_match_all(self::REGEX_PATTERN, $templatePattern, $this->allKeyThatNeedToReplace);
        return $this->allKeyThatNeedToReplace[1];
    }

    /**
     * Description : use to get all additional file for custom information from defined directory
     * @return array
     */
    public function getAllAdditionalFile(): array
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
    public function getAdditionalClassInstance(string $filename): ?object
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
