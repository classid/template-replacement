<?php

namespace Classid\TemplateReplacement\Traits;

trait HasInformation
{
    protected array $methodParams;

    /**
     * @param string $key
     * @return mixed
     */
    public function getParameter(string $key): mixed
    {
        if (isset($this->methodParams[$key])) {
            return $this->methodParams[$key];
        }

        return null;
    }
}
