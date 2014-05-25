<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Main\Entity;

/**
 * Scalar entity field class for non-array and non-object data types
 * @package bitrix
 * @subpackage main
 */
class ScalarField extends Field
{
	protected $is_primary;

	protected $is_unique;

	protected $is_required;

	protected $is_autocomplete;

	protected $column_name = '';

	/** @var null|callable|mixed  */
	protected $default_value;

	public function __construct($name, $dataType, Base $entity, $parameters = array())
	{
		parent::__construct($name, $dataType, $entity, $parameters);

		$this->is_primary = (isset($parameters['primary']) && $parameters['primary']);
		$this->is_unique = (isset($parameters['unique']) && $parameters['unique']);
		$this->is_required = (isset($parameters['required']) && $parameters['required']);
		$this->is_autocomplete = (isset($parameters['autocomplete']) && $parameters['autocomplete']);

		$this->column_name = isset($parameters['column_name']) ? $parameters['column_name'] : $this->name;
		$this->default_value = isset($parameters['default_value']) ? $parameters['default_value'] : null;
	}

	public function isPrimary()
	{
		return $this->is_primary;
	}

	public function isRequired()
	{
		return $this->is_required;
	}

	public function isUnique()
	{
		return $this->is_unique;
	}

	public function isAutocomplete()
	{
		return $this->is_autocomplete;
	}

	public function getColumnName()
	{
		return $this->column_name;
	}

	static public function isValueEmpty($value)
	{
		return (strval($value) === '');
	}

	/**
	 * @return callable|mixed|null
	 */
	public function getDefaultValue()
	{
		if (is_callable($this->default_value))
		{
			return call_user_func($this->default_value);
		}
		else
		{
			return $this->default_value;
		}
	}
}
