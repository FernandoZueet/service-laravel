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
	 * Create new query
	 *
	 * @return void
	 */
	protected function newQuery()
	{
		$this->modelClass = new $this->modelClass();

		return $this->modelClass;
	}

	/**
	 * Error query
	 *
	 * @param \Exception $e
	 * @param boolean $transaction
	 * @return void
	 */
	protected function errorQuery(\Exception $e, bool $transaction = false)
	{
		if ($transaction) {
			$this->rollBack();
		}

		return false;
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
		try {

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
		} catch (\Exception $e) {
			return $this->errorQuery($e);
		}
	}

	/**
	 * Insert row(s)
	 *
	 * @param array $values
	 * @param boolean $transaction
	 * @return void
	 */
	public function insert(array $values, bool $transaction = false, array $exclude = [], array $include = [])
	{
		try {

			$this->newQuery();

			return $this->setValuesModel($values)->saveModel($exclude, $include);
		} catch (\Exception $e) {
			return $this->errorQuery($e, $transaction);
		}
	}

	/**
	 * Update row(s) by id
	 *
	 * @param integer $id
	 * @param array $values
	 * @param boolean $transaction
	 * @param array $exclude
	 * @param array $include
	 * @return void
	 */
	public function updateById(int $id, array $values, bool $transaction = false, array $exclude = [], array $include = [])
	{
		try {

			$this->newQuery();

			$this->modelClass = $this->modelClass->where('id', $id)->first();
			if (!$this->modelClass) {
				return false;
			}

			return $this->setValuesModel($values)->saveModel($exclude, $include);
		} catch (\Exception $e) {
			return $this->errorQuery($e, $transaction);
		}
	}

	/**
	 * Soft delete row(s) by id
	 *
	 * @param integer $id
	 * @param boolean $transaction
	 * @return void
	 */
	public function softDeleteById(int $id, bool $transaction = false)
	{
		try {

			$this->newQuery();

			return $this->modelClass->where('id', $id)->delete();
		} catch (\Exception $e) {
			return $this->errorQuery($e, $transaction);
		}
	}

	/**
	 * Delete row(s) by id
	 *
	 * @param integer $id
	 * @param boolean $transaction
	 * @return void
	 */
	public function deleteById(int $id, bool $transaction = false)
	{
		try {

			$this->newQuery();

			return $this->modelClass->where('id', $id)->forceDelete();
		} catch (\Exception $e) {
			return $this->errorQuery($e, $transaction);
		}
	}
}
