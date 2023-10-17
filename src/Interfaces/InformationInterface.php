<?php

namespace Classid\TemplateReplacement\Interfaces;

interface InformationInterface
{
    public function __construct(array $methodParams = []);
    public function getParameter(string $key):mixed;
}
