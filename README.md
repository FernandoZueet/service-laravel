# Service laravel

Library that helps to separate the business rule part of the control. Leaving control leaner.

---

## Requirements

-   PHP 7.0 or newer;
-   Laravel 5.5 or newer;

---

## Installation

```bash
$ composer require fernandozueet/service-laravel
```

---

## Register the commands

File: <code>app/Console/Kernel.php</code>

```php
protected $commands = [
    \FzService\Console\ServiceCommand::class,
    \FzService\Console\ResourceCommand::class,
];
```

---

## Create a class of resource

The resource class is overwritten and gets a new customization feature from the fields that are returned.

You do not need to enter the resource prefix.

```bash
$ php artisan fzservice:make:resource User
```

Class created:

```php
<?php

namespace App\Http\Resources;

use FzService\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
    	$return = parent::toArray($request);

    	return $this->mountFields($return);
    }
}
```

How to use the filter?

```php
\App\Resources\UserResource::collection(\App\User::paginate(), ['fields' => 'user' => 'id,name']);
```

---

## Create a class of service

You do not need to enter the service prefix.

```bash
$ php artisan fzservice:make:service User
```

Class created:

```php
<?php

namespace App\Services;

use FzService\Service;
use App\Models\User;
use App\Http\Resources\UserResource;

class UserService extends Service
{
    /**
    * Model class
    *
    * @var \App\Models\User
    */
    protected $modelClass = User::class;

    /**
     * Read all rows
     *
     * @param array $params
     * @param boolean $collection
     * @return array|stdClass
     */
    public function readAll(array $params = [], bool $collection = true)
    {
    	return $this->mountRead(function() use ($params) {

            //

        }, $params, $collection ? UserResource::class : null, []);
    }

}
```

---

## Service Read

Method to assist in the assembly of queries eloquent.

```php
<?php

namespace App\Services;

use FzService\Service;
use App\Models\User;
use App\Http\Resources\UserResource;

class UserService extends Service
{
    /**
    * Model class
    *
    * @var \App\Models\User
    */
    protected $modelClass = User::class;

    /**
     * Read all rows
     *
     * @param array $params
     * @param boolean $collection
     * @return array|stdClass
     */
    public function readAll(array $params = [], bool $collection = true)
    {
    	return $this->mountRead(function() use ($params) {

            //wheres
            if(!empty($params['id'])) {
                $this->modelClass = $this->modelClass->where('id', $params['id']);
            }
            if(!empty($params['name'])) {
                $this->modelClass = $this->modelClass->where('name', $params['name']);
            }

            //

        }, $params, $collection ? UserResource::class : null, []);
    }

}
```

Example 1:

<code>Searching by name on page 1</code>

```php
//Service
$userService = \App\Services\UserService();
$result = $userService->readAll(['name' => "User test", 'page' => 1]);

//Code equal to:
$result = \App\Resources\UserResource::collection(\App\User::where('name',$params['name'])->paginate());
```

Example 2:

<code>Searching by name without pagination</code>

```php
//Service
$userService = \App\Services\UserService();
$result = $userService->readAll(['name' => "User test"]);

//Code equal to:
$result = \App\Resources\UserResource::collection(\App\User::where('name',$params['name'])->get());
```

Example 3:

<code>Searching by name and sorting by name and last_name ascending</code>

```php
//Service
$userService = \App\Services\UserService();
$result = $userService->readAll(['name' => "User test", 'sort' => 'name,last_name']);

//Code equal to:
$result = \App\Resources\UserResource::collection(\App\User::where('name',$params['name'])->orderBy('name','ASC')->orderBy('last_name','ASC')->get());
```

Example 4:

<code>Searching by name and sorting by name downward</code>

```php
//Service
$userService = \App\Services\UserService();
$result = $userService->readAll(['name' => "User test", 'sort' => '-name']);

//Code equal to:
$result = \App\Resources\UserResource::collection(\App\User::where('name',$params['name'])->orderBy('name','DESC')->get());
```

Example 5:

<code>Searching by name on page 1 and choosing the return fields</code>

```php
//Service
$userService = \App\Services\UserService();
$result = $userService->readAll(['name' => "User test", 'page' => 1, 'fields' => [ 'user' => 'id,name' ] ]);

//Code equal to: (this option does not exist in the default laravel resource class)
$result = \App\Resources\UserResource::collection(\App\User::where('name',$params['name'])->paginate());
```

Disabling default methods:

Default methods: <code>paginate and sort</code>

```php
public function readAll(array $params = [], bool $collection = true)
{
    return $this->mountRead(function() use ($params) {

        //

    }, $params, $collection ? UserResource::class : null, ['paginate','sort']);
}
```

Disabling Resource Class:

To disable the resource class simply enter the null value.

```php
public function readAll(array $params = [], bool $collection = true)
{
    return $this->mountRead(function() use ($params) {

        //

    }, $params, null, []);
}
```

---

## Service Insert

Method to insertion data eloquent.

<code>insert(array $values, bool $transaction = false, array $exclude = [], array $include = [])</code>

Example 1:

<code>Inserting and returning an array with the entered data</code>

```php
//Service
$userService = \App\Services\UserService();
$result = $userService->insert(['name' => "User test", "last_name" => 'other name']);

//Code equal to:
$model = new \App\User();
$model->name = 'User test';
$model->last_name = 'other name';
$model->save();
$result = $model->toArray();
```

Example 2:

<code>
Inserting and returning an array with the entered data.

Deleting or adding a field from the returned data.</code>

```php
//Service - exclude return fields
$userService = \App\Services\UserService();
$result = $userService->insert(['name' => "User test", "last_name" => 'other name'], false, ['id','name']);

//Service - visible return fields
$userService = \App\Services\UserService();
$result = $userService->insert(['name' => "User test", "last_name" => 'other name'], false, [], ['id','name']);

//Code equal to:
$model = new \App\User();
$model->name = 'User test';
$model->last_name = 'other name';
$model->save();
$result = $model->makeHidden(['id','name'])->toArray(); //exclude return fields
$result = $model->makeVisible(['id','name'])->toArray(); //visible return fields
```

---

## Service Update

Method to update data eloquent.

<code>updateById(int $id, array $values, bool $transaction = false, array $exclude = [], array \$include = [])</code>

Example:

```php
//Service
$userService = \App\Services\UserService();
$result = $userService->updateById(1, ['name' => "New name"]);

//Code equal to:
$model = \App\User::where('id', 1);
$model->name = 'New name';
$model->save();
$result = $model->toArray();
```

---

## Service Delete

Method to delete data eloquent.

<code>deleteById(int $id, bool $transaction = false)</code>

Example:

```php
//Service
$userService = \App\Services\UserService();
$result = $userService->deleteById(1);

//Code equal to:
$result = \App\User::where('id', 1)->forceDelete();
```

---

## Service Soft Delete

Method to soft delete data eloquent.

<code>softDeleteById(int $id, bool $transaction = false)</code>

Example:

```php
//Service
$userService = \App\Services\UserService();
$result = $userService->softDeleteById(1);

//Code equal to:
$result = \App\User::where('id', 1)->delete();
```

---

## Useful methods

File: <code>\FzService\Service.php</code>

```php
//Model
$this->modelClass;

//Create eloquent model instance
$this->newQuery();

//Set value field (array $params)
$this->setValuesModel(['name' => 'User test']);

//Save model (array $exclude = [], array $include = [], bool $returnArray = true)
$this->saveModel();
```

---

## Contributing

Please see [CONTRIBUTING](https://github.com/FernandoZueet/service-laravel/graphs/contributors) for details.

## Security

If you discover security related issues, please email fernandozueet@hotmail.com instead of using the issue tracker.

## Credits

-   [Fernando Zueet](https://github.com/FernandoZueet)

## License

The package is licensed under the MIT license. See [License File](LICENSE.md) for more information.
