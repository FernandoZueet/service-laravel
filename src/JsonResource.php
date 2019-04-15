<?php

/**
 * This file is part of the FzService package
 *
 * @link http://github.com/fernandozueet/service-laravel
 * @copyright 2019
 * @license MIT License
 * @author Fernando Zueet <fernandozueet@hotmail.com>
 */

namespace FzService;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource as JsonResourceOrigin;

class JsonResource extends JsonResourceOrigin
{
	/**
	 * Fields
	 *
	 * @var array
	 */
	private static $fields = [];

	/**
	 * Create new anonymous resource collection.
	 *
	 * @param mixed $resource
	 * @param array $fields
	 * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
	 */
    public static function collection($resource, array $fields = [])
    {
		self::$fields = $fields;

        return tap(new AnonymousResourceCollection($resource, static::class), function ($collection) {
            if (property_exists(static::class, 'preserveKeys')) {
                $collection->preserveKeys = (new static([]))->preserveKeys === true;
            }
        });
    }

	/**
	 * Mount fields.
	 * Ex: http://127.0.0.1:8000/api/profiles?fields[profile]=id,name,profile_category&fields[profile_category]=id
	 *
	 * @param array $return
	 * @return array
	 */
	public function mountFields(array $return): array
	{
		if(isset(self::$fields['fields'])) {
			$params = self::$fields['fields'];
		}else{
			$params = self::$fields;
		}

		if(empty($params)) {
			return $return;
		}

		$resourceName = explode('\\', get_class($this));
		$resourceName = snake_case(end($resourceName));
		$resourceName = str_replace('_resource', "", $resourceName);

		//new array
		if(isset($params[$resourceName])) {
			$fields = explode(',', $params[$resourceName]);

			$newArray = [];
			foreach ($fields as $key1 => $value1) {
				$result = array_get($return, $value1);
				if($result) {
					array_set($newArray, $value1, $result);
				}
			}

			if(empty($newArray)) {
				return $return;
			}

			return $newArray;
		}

		//all
		return $return;
	}

}
