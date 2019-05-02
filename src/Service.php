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

use Illuminate\Support\Facades\DB;

abstract class Service
{
	//-----------------------------------------------------------------------------
	// General
	//-----------------------------------------------------------------------------

	/**
	 * Model class for repo.
	 *
	 * @var string
	 */
	protected $modelClass;

	/**
     * Model class for repo.
     *
     * @var string
     */
    protected $modelClassA;

	/**
	 * Create new query
	 *
	 * @return void
	 */
	protected function newQuery()
	{
        if(empty($this->modelClassA)) {
            $this->modelClass = new $this->modelClass();
            $this->modelClassA = $this->modelClass;
        }else{
            $this->modelClass = $this->modelClassA;
        }

		return $this->modelClass;
	}

	/**
	 * Set values model
	 *
	 * @param array $params
	 * @param object $model
	 * @return void
	 */
	protected function setValuesModel(array $params)
	{
		foreach ($params as $key => $value) {
			$this->modelClass[$key] = $value;
		}

		return $this;
	}

	/**
	 * Save model
	 *
	 * @param array $exclude
	 * @param array $include
	 * @param boolean $returnArray
	 * @return void
	 */
	protected function saveModel(array $exclude = [], array $include = [], bool $returnArray = true)
	{
		$this->modelClass->save();

		return $this->filtersModel($exclude, $include, $returnArray);
	}

	/**
	 * Filters model
	 *
	 * @param array $exclude
	 * @param array $include
	 * @param boolean $returnArray
	 * @return void
	 */
	protected function filtersModel(array $exclude = [], array $include = [], bool $returnArray = true) 
	{
		if (!empty($exclude)) {
			$this->modelClass = $this->modelClass->makeHidden($exclude);
		}

		if (!empty($include)) {
			$this->modelClass = $this->modelClass->makeVisible($include);
		}

		if ($returnArray) {
			return $this->modelClass->toArray();
		}

		return $this;
	}

	/**
	 * Begin transaction
	 *
	 * @return void
	 */
	public function beginTransaction()
	{
		DB::beginTransaction();
	}

	/**
	 * Roll back
	 *
	 * @return void
	 */
	public function rollBack()
	{
		DB::rollBack();
	}

	/**
	 * Commit
	 *
	 * @return void
	 */
	public function commit()
	{
		DB::commit();
	}

	//-----------------------------------------------------------------------------
	// Crud
	//-----------------------------------------------------------------------------

	/**
	 * Mounted read
	 *
	 * @param mixed $function
	 * @param array $params
	 * @param object $collection
	 * @param array $disabledMethods
	 * @return void
	 */
	protected function mountRead($function = null, array $params, $collection = null, array $disabledMethods = [])
	{
		$this->newQuery();

		if ($function) {
			$function();
		}

		//sort
		if (!in_array('sort', $disabledMethods)) {
			if (!empty($params['sort'])) {
				$sorts = explode(',', $params['sort']);
				foreach ($sorts as $key => $value) {
					$field = $value;
					if ($value[0] == '-') {
						$sort = 'DESC';
						$field = str_replace('-', '', $field);
					} else {
						$sort = 'ASC';
					}
					$this->modelClass = $this->modelClass->orderBy($field, $sort);
				}
			}
		}

		//paginate
		if (!in_array('paginate', $disabledMethods)) {
			if (!empty($params['page'])) {
				$this->modelClass = $this->modelClass->paginate($params['items'] ?? null, ['*'], 'page', $params['page']);
			} else {
				$this->modelClass = $this->modelClass->get();
			}
		}

		//return
		if ($collection) {
			$result = $collection::collection($this->modelClass, $params['fields'] ?? []);
			$result = $result->toResponse($result);

			return $result->getData();
		} else {
			return $this->modelClass->toArray();
		}
	}

	/**
	 * Create row(s)
	 *
	 * @param array $values
	 * @param array $exclude
	 * @param array $include
	 * @return void
	 */
	public function create(array $values, array $exclude = [], array $include = [])
	{
		$this->newQuery();

		return $this->modelClass->create($values)->filtersModel($exclude, $include);		
	}

	/**
	 * Insert row(s)
	 *
	 * @param array $values
	 * @param array $exclude
	 * @param array $include
	 * @return void
	 */
	public function insert(array $values, array $exclude = [], array $include = [])
	{
		$this->newQuery();

		return $this->setValuesModel($values)->saveModel($exclude, $include);		
	}

	/**
	 * Update row(s) by id
	 *
	 * @param integer $id
	 * @param array $values
	 * @param array $exclude
	 * @param array $include
	 * @return void
	 */
	public function updateById(int $id, array $values, array $exclude = [], array $include = [])
	{
		$this->newQuery();

		$this->modelClass = $this->modelClass->where('id', $id)->first();
		if (!$this->modelClass) {
			return false;
		}

		return $this->setValuesModel($values)->saveModel($exclude, $include);
	}

	/**
	 * Soft delete row(s) by id
	 *
	 * @param integer $id
	 * @return void
	 */
	public function softDeleteById(int $id)
	{
		$this->newQuery();

		return $this->modelClass->where('id', $id)->delete();
	}

	/**
	 * Delete row(s) by id
	 *
	 * @param integer $id
	 * @return void
	 */
	public function deleteById(int $id)
	{
		$this->newQuery();

		return $this->modelClass->where('id', $id)->forceDelete();
	}

}
