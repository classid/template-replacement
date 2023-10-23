<?php

namespace Classid\TemplateReplacement\Abstracts;

use Classid\TemplateReplacement\Interfaces\InformationInterface;
use Classid\TemplateReplacement\Traits\HasInformation;

abstract class BaseInformation implements InformationInterface
{
    use HasInformation;
    public function __construct(array $methodParams = [])
    {
        $this->methodParams = $methodParams;
    }
}
