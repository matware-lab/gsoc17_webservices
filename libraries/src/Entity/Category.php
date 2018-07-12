<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Entity;

use Joomla\Entity\Model;
use Joomla\Entity\Relations\Relation;

defined('JPATH_PLATFORM') or die;

/**
 * Entity Model for a Category.
 *
 * @since  __DEPLOY_VERSION__
 */
class Category extends Model
{
	use EntityTableTrait;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = '#__categories';

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'metadata' => 'array',
		'params' => 'array',
		'rules' => 'array'
	];

	/**
	 * The attributes that should be mutated to dates. Already aliased!
	 *
	 * @var array
	 */
	protected $dates = [
		'created',
		'modified',
		'checked_out_time',
		'publish_up',
		'publish_down'
	];

	/**
	 * Array with alias for "special" columns such as ordering, hits etc etc
	 *
	 * @var    array
	 */
	protected $columnAlias = [
		'createdAt' => 'created_time',
		'updatedAt' => 'modified_time'
	];

	/**
	 * Get the articles from the current category.
	 * @return Relation
	 */
	public function articles()
	{
		return $this->hasMany('Joomla\CMS\Entity\Content', 'catid');
	}

}
