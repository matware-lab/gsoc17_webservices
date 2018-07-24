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
 * Entity Model for Rating.
 *
 * @since  __DEPLOY_VERSION__
 */
class Rating extends Model
{
	use EntityTableTrait;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = '#__content_rating';

	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $primaryKey = 'content_id';

	/**
	 * Indicates if the IDs are auto-incrementing.
	 *
	 * @var boolean
	 */
	public $incrementing = false;

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var boolean
	 */
	public $timestamps = false;

	/**
	 * Get the articles from the current category.
	 * @return Relation
	 */
	public function article()
	{
		return $this->belongsTo('Joomla\CMS\Entity\Content', 'article');
	}

	/**
	 * Mutation for rating
	 * @return mixed|string
	 */
	public function getRating()
	{
		if (!$this->rating_count)
		{
			return 0;
		}

		return round($this->rating_sum / $this->rating_count);
	}
}
