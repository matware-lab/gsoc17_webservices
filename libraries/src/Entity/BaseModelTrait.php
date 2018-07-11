<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Entity;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Entity\Model;
use Joomla\Utilities\ArrayHelper;

/**
 * Base Model Entity Trait
 *
 * Acts as a Factory class for application specific objects and provides many supporting API functions.
 *
 * @since  2.5.5
 */
trait BaseModelTrait
{
	/**
	 * Method to load a row for editing from the version history table.
	 *
	 * @param   integer  $version_id  Key to the version history table.
	 * @param   Table    &$table      Content table object being loaded.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 *
	 * @since   3.2
	 * @todo needs refactoring
	 */
	public function loadHistory($version_id, Table &$table)
	{
		// Only attempt to check the row in if it exists, otherwise do an early exit.
		if (!$version_id)
		{
			return false;
		}

		// Get an instance of the row to checkout.
		$historyTable = Table::getInstance('Contenthistory');

		if (!$historyTable->load($version_id))
		{
			// TODO throw error here
			// $this->setError($historyTable->getError());

			return false;
		}

		$rowArray = ArrayHelper::fromObject(json_decode($historyTable->version_data));
		$typeId   = Table::getInstance('Contenttype')->getTypeId($this->typeAlias);

		if ($historyTable->ucm_type_id != $typeId)
		{
			// TODO throw error here
			// $this->setError(\JText::_('JLIB_APPLICATION_ERROR_HISTORY_ID_MISMATCH'));

			$key = $table->getKeyName();

			if (isset($rowArray[$key]))
			{
				$table->checkIn($rowArray[$key]);
			}

			return false;
		}

		$this->setState('save_date', $historyTable->save_date);
		$this->setState('version_note', $historyTable->version_note);

		return $table->bind($rowArray);
	}

	/**
	 * Method to check if the given record is checked out by the current user
	 *
	 * @param   Model  $item  The record to check
	 *
	 * @return  boolean
	 */
	public function isCheckedOut($item = null)
	{
		$item = ($item !== null) ?: $this;

		if ($item->hasField('checked_out') && $item->checked_out != \JFactory::getUser()->id)
		{
			return true;
		}

		return false;
	}

	/**
	 * Boots the component with the given name.
	 *
	 * @param   string  $component  The component name, eg. com_content.
	 *
	 * @return  ComponentInterface  The service container
	 *
	 * @since   4.0.0
	 * @throws \Exception
	 */
	protected function bootComponent($component): ComponentInterface
	{
		return Factory::getApplication()->bootComponent($component);
	}

	/**
	 * Clean the cache
	 *
	 * @param   string  $group  The cache group
	 *
	 * @return  void
	 *
	 * @since   3.0
	 * @throws \Exception
	 */
	protected function cleanCache($group = null)
	{
		$conf = \JFactory::getConfig();

		$options = [
			'defaultgroup' => $group ?: ($this->option ?? \JFactory::getApplication()->input->get('option')),
			'cachebase'    => $conf->get('cache_path', JPATH_CACHE),
			'result'       => true,
		];

		try
		{
			/** @var \JCacheControllerCallback $cache */
			$cache = \JCache::getInstance('callback', $options);
			$cache->clean();
		}
		catch (\JCacheException $exception)
		{
			$options['result'] = false;
		}
	}
}
