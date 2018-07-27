<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Model;

use Joomla\Entity\Helpers\Collection;

defined('_JEXEC') or die;

/**
 * Methods supporting a list of featured article records.
 *
 * @since  1.6
 */
class FeaturedModel extends ArticlesModel
{
	/**
	 * Gets the Articles Collection
	 *
	 * @return  Collection
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getCollection()
	{
		$this->setAlias('c');

		$column = $this->feature()->getRelated()->getQualifiedPrimaryKey();
		$this->filter('feature',
			function ($query) use ($column)
			{
				$query->where("$column IS NOT NULL");
			}
		);

		return parent::getCollection();
	}
}
