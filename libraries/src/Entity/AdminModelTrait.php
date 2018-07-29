<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Entity;

defined('JPATH_PLATFORM') or die;

use Exception;
use Joomla\Entity\Model;
use Joomla\String\StringHelper;

/**
 * Admin Model Entity Traint
 *
 * @since  1.6
 */
trait AdminModelTrait
{
	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since   1.6
	 */
	protected function canDelete($record)
	{
		return \JFactory::getUser()->authorise('core.delete', $this->option);
	}

	/**
	 * Method to test whether a record can have its state changed.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   1.6
	 */
	protected function canEditState($record)
	{
		return \JFactory::getUser()->authorise('core.edit.state', $this->option);
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   1.6
	 * @throws \Exception
	 * @todo do we want to make this resilient to errors (throw exceptions at the end of the execution?)
	 */
	public function deleteRows(&$pks)
	{
		$pks = (array) $pks;

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			/** TODO if we want to keep constraint loading, we can optimize this loads to only query the primary key.
			 * This is inefficient, deleting should not require loading the Model first.
			 */
			if ($this->load($pk))
			{
				if ($this->canDelete($this))
				{
					$context = $this->option . '.' . $this->name;

					// TODO associations

					if (!$this->delete())
					{
						// TODO better exception
						throw new \Exception("failed to delete");
					}
				}
				else
				{
					// Prune items that you can't change.
					unset($pks[$i]);

					\JLog::add(\JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), \JLog::WARNING, 'jerror');
				}
			}
			else
			{
				// TODO better exception
				throw new \Exception("failed to load entity for delete");
			}
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  Model|boolean  Object on success, false on failure.
	 *
	 * @since   1.6
	 * @throws \Exception
	 */
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getPrimaryKeyValue();

		if ($pk > 0)
		{
			// Attempt to load the row.
			if ($this->load($pk) === false)
			{
				throw new \Exception("error");
			}

			return $this;
		}

		return false;
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   Model  $model  A Model object.
	 *
	 * @return  array  An array of conditions to add to ordering queries.
	 *
	 * @since   1.6
	 */
	protected function getReorderConditions($model)
	{
		return array();
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array    &$pks   A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 * @throws \Exception
	 */
	public function publish(&$pks, $value = 1)
	{
		$pks = (array) $pks;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			$pks = array();

			// TODO we do not support composed primary keys.
			if ($this->getPrimaryKeyValue())
			{
				$pks[] = $this->getPrimaryKeyValue();
			}
			else
			{
				throw new Exception(\JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
			}
		}

		$state  = (int) $value;

		// Access checks and attempt to change the state of the records.
		foreach ($pks as $i => $pk)
		{
			if ($this->load($pk, true))
			{
				if (!$this->canEditState($this))
				{
					// Prune items that you can't change.
					unset($pks[$i]);

					\JLog::add(\JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), \JLog::WARNING, 'jerror');

					return false;
				}

				// If the table is checked out by another user, drop it and report to the user trying to change its state.
				if ($this->isCheckedOut())
				{
					\JLog::add(\JText::_('JLIB_APPLICATION_ERROR_CHECKIN_USER_MISMATCH'), \JLog::WARNING, 'jerror');

					return false;
				}

				$this->published = $state;

				// If publishing, set published date/time if not previously set
				if ($state && $this->hasField('publish_up') && (int) $this->publish_up == 0)
				{
					$this->publish_up = \JFactory::getDate()->toSql();
				}

				// Determine if there is checkin support for the model.
				$checkin = $this->hasCheckin();

				// Checkin only if we publish one item.
				if ($checkin && (count($pks) == 1))
				{
					$this->checked_out = '0';
					$this->checked_out_time = $this->getDb()->getNullDate();
				}
				$this->persist();
			}
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to adjust the ordering of a row.
	 *
	 * Returns NULL if the user did not have edit
	 * privileges for any of the selected primary keys.
	 *
	 * @param   integer  $pks    The ID of the primary key to move.
	 * @param   integer  $delta  Increment, usually +1 or -1
	 *
	 * @return  boolean|null  False on failure or error, true on success, null if the $pk is empty (no items selected).
	 *
	 * @since   1.6
	 */
	public function reorder($pks, $delta = 0)
	{
		// TODO reorder

		return true;
	}


	/**
	 * Method to compact the ordering values of rows in a group of rows defined by an SQL WHERE clause.
	 *
	 * @param   Model   $entity  Entity used for reordering
	 * @param   string  $where   WHERE clause to use for limiting the selection of rows to compact the ordering values.
	 *
	 * @return  mixed  Boolean  True on success.
	 *
	 * @since   11.1
	 * @throws  \UnexpectedValueException
	 */
	public function reorderAll($entity, $where = '')
	{
		// Check if there is an ordering field set
		$orderingField = $entity->getColumnAlias('ordering');

		if (!$entity->hasField($orderingField))
		{
			throw new \UnexpectedValueException(sprintf('%s does not support ordering.', get_class($this)));
		}

		$db = $entity->getDb();

		$quotedOrderingField = $db->quoteName($orderingField);

		$subquery = $db->getQuery(true)
			->from($entity->getTableName())
			->selectRowNumber($quotedOrderingField, 'new_ordering');

		$query = $db->getQuery(true)
			->update($entity->getTableName())
			->set($quotedOrderingField . ' = sq.new_ordering');

		$innerOn = array();

		// Get the primary keys for the selection. TODO we only support one primary key

		$subquery->select($db->quoteName($entity->getPrimaryKey(), "pk"));
		$innerOn[] = $db->quoteName($entity->getPrimaryKey()) . ' = sq.' . $db->quoteName("pk");

		// Setup the extra where and ordering clause data.
		if ($where)
		{
			$subquery->where($where);
			$query->where($where);
		}

		$subquery->where($quotedOrderingField . ' >= 0');
		$query->where($quotedOrderingField . ' >= 0');

		$query->innerJoin('(' . (string) $subquery . ') AS sq ON ' . implode(' AND ', $innerOn));

		$db->setQuery($query);
		$db->execute();

		return true;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data       The form data.
	 * @param   array  $relations  The relations associated with this entity.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   1.6
	 * @throws \Exception
	 */
	public function save(array $data, array $relations)
	{
		$key = $this->getPrimaryKey();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');

		// Load the row if saving an existing record.
		if ($pk > 0)
		{
			$this->load($pk);
		}
		else
		{
			$this->reset();
		}

		// Bind the data.
		$this->bind($data);

		// TODO Check the data.
		if (!$this->check())
		{
			throw new \Exception("check failed");
		}

		// Store the data.
		foreach ($relations as $relation)
		{
			if (!$relation->save($this))
			{
				// TODO better exception
				throw new \Exception("persist failed");
			}
		}

		if (!$this->persist())
		{
			// TODO better exception
			throw new \Exception("persist failed");
		}

		// Clean the cache.
		$this->cleanCache();

		// TODO associations

		return true;
	}

	/**
	 * Saves the manually set order of records.
	 *
	 * @param   array    $pks    An array of primary key ids.
	 * @param   integer  $order  +1 or -1
	 *
	 * @return  boolean  Boolean true on success, false on failure
	 *
	 * @since   1.6
	 */
	public function saveorder($pks = array(), $order = null)
	{
		// TODO saveorder

		return true;
	}

	/**
	 * Method to change the title & alias.
	 *
	 * @param   integer        $category_id  The id of the category.
	 * @param   string         $alias        The alias.
	 * @param   string         $title        The title.
	 * @param   boolean/array  $rows         False if query needs to be done, array of results otherwise.
	 *
	 * @return	array  Contains the modified title and alias.
	 *
	 * @since	1.7
	 */
	protected function generateNewTitle($category_id, $alias, $title, $rows = false)
	{
		$rows = (is_array($rows)) ?: $this->where(['alias' => $alias, 'catid' => $category_id])->first();

		// Alter the title & alias
		foreach ($rows as $row)
		{
			$title = StringHelper::increment($title);
			$alias = StringHelper::increment($alias, 'dash');
		}

		return array($title, $alias);
	}


	/**
	 * Method to check is the current Model has checking support
	 *
	 * @return boolean
	 */
	protected function hasCheckin()
	{
		return ($this->hasField('checked_out') || $this->hasField('checked_out_time'));
	}
}
