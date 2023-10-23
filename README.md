# Template Replacement
Template replacement use to replace string template using available method, property, or data from params.

# How To Install
Minimal php version is 8.0
Since this package is not publish in packagist yet, you noeed to add source of this pckage on your **composer.json** file
You can install this package via composer with 

```json
"repositories":[
  {
    "type": "vcs",
    "url": "git@github.com:classid/template-replacement.git"
  }
],
```
```
composer require classid/template-replacement
```

# How to use
This is example how to use template replacement. There are 3 ways to replace placeholder with data variable. 
- With priorityReplacementData on third parameter. This data will be replace placeholder as first priority
- With public property from class service that defined on specified directory. 
- With public method from class service that defined on specified directory. Yoy can change directory via **config/templatereplacement.php**
This use case is by hierarchy, when the data is already exists via parameter, data from property and method will not read

## Using third parameter, priorityReplacementData
You can pass array for priorityReplacementData on third parameter or using named argument. Array key must same as placeholder name. 

```php

TemplateReplacement::execute("Halo {target}, my name is {name}", priorityReplacementData: ["name" => "Iqbal"])

```

## Using public property on specified class
You can use public property on specified class. You can modify class via config

```php
<?php

namespace App\Services\GeneralReplacement;

use Classid\TemplateReplacement\Abstracts\BaseInformation;

class WhatEverClassNameAsLongAsInsideDirectoryAndNamespace extends BaseInformation{
    public string $name = "iqbal"
    public string $target = "everyone"
}


TemplateReplacement::execute("Halo {target}, my name is {name}")
```

## Using public method on specified class
You also can use public method on specified class. You can modify class via config
```php
<?php

namespace App\Services\GeneralReplacement;

use Classid\TemplateReplacement\Abstracts\BaseInformation;

class WhatEverClassNameAsLongAsInsideDirectoryAndNamespace extends BaseInformation{
    public function getName(){
      return "iqbal";
    }

    public function getTarget(){
      return "everyone";
    }
}


TemplateReplacement::execute("Halo {target}, my name is {name}");
```

> [!IMPORTANT]
> There is naming convention for using method. Placeholdername is using snake case, for example full_name, and the method is using camel case with prefix "get", for example getFullName()


> [!NOTE]
> What if you want to using query on method ? Yes you can passing parameter on second param as data that can accessed by method

```php
<?php

namespace App\Services\GeneralReplacement;

use Classid\TemplateReplacement\Abstracts\BaseInformation;

class WhatEverClassNameAsLongAsInsideDirectoryAndNamespace  extends BaseInformation{
    public function getName(){
      $userId = $this->getParameter("user_id");
      $user = App\Models\User::find($userId);
      return "iqbal";
    }

    public function getTarget(){
      return "everyone";
    }
}


TemplateReplacement::execute("Halo {target}, my name is {name}", ["user_id" => 1]);
```

