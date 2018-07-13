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
 * Entity Model for a ViewLevel.
 *
 * @since  __DEPLOY_VERSION__
 */
class ViewLevel extends Model
{
	use EntityTableTrait;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = '#__viewlevels';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var boolean
	 */
	public $timestamps = false;

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'rules' => 'array'
	];
}
