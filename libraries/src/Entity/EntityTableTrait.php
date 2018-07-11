<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Entity;

use Joomla\Database\DatabaseDriver;

defined('JPATH_PLATFORM') or die;

/**
 * Trait to apply to the Joomla Entity system which allows it to implement \Joomla\CMS\Table\TableInterface
 *
 * @since  __DEPLOY_VERSION__
 */
trait EntityTableTrait
{
	/**
	 * Wrapper for getPrimaryKey
	 *
	 * @return mixed
	 */
	public function getKeyName()
	{
		return $this->getPrimaryKey();
	}

	/**
	 * Reset function
	 * Will not throw an error if the column does not exist during reset, but it will be thrown when saving the model.
	 *
	 * @param   array          $attributes  pre loads any attributed for the model (user friendly format)
	 *
	 * @return void
	 */
	public function reset(array $attributes = [])
	{
		$this->exists = false;

		$this->attributesRaw = [];

		if ($attributes)
		{
			$this->setAttributes($attributes);
		}

		$this->syncOriginal();
	}

	/**
	 * Load a row in the current insance
	 *
	 * @param   mixed    $key    primary key, if there is no key, then this is used for a new item, therefore select last
	 * @param   boolean  $reset  reset flag
	 *
	 * @return boolean
	 */
	public function load($key = null, $reset = true)
	{
		$key = ($key) ?: $this->getPrimaryKeyValue();

		if ($key === null)
		{
			throw new \UnexpectedValueException('Null primary key not allowed.');
		}

		$query = $this->newQuery();

		if ($reset)
		{
			$this->reset();
		}

		if (!$attributes = $query->selectRaw($key))
		{
			return false;
		}

		$this->setAttributes($attributes);

		$this->exists = true;

		$this->syncOriginal();

		return true;
	}

	/**
	 * Check function. This need to be overwritten in the Entity class.
	 *
	 * @return boolean
	 */
	public function check()
	{
		return true;
	}

	/**
	 * Bind function, useful because it only sets existing attributes
	 *
	 * @param   array  $src     assoc array of values for binding
	 * @param   array  $ignore  keys to be ignored
	 *
	 * @return boolean
	 */
	public function bind($src, $ignore = array())
	{
		if (is_string($ignore))
		{
			$ignore = explode(' ', $ignore);
		}

		// Bind the source value, excluding the ignored fields.
		foreach ($this->getAttributes() as $k => $v)
		{
			// Only process fields not in the ignore array.
			if (!in_array($k, $ignore))
			{
				if (isset($src[$k]))
				{
					$this->setAttribute($k, $src[$k]);
				}
			}
		}

		return true;
	}
}
