<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\View\Featured;

defined('_JEXEC') or die;

use Joomla\Component\Content\Administrator\View\Articles\HtmlView as ArticlesHtmlView;

\JLoader::register('ContentHelper', JPATH_ADMINISTRATOR . '/components/com_content/helpers/content.php');

/**
 * View class for a list of featured articles.
 *
 * @since  1.6
 */
class HtmlView extends ArticlesHtmlView
{
	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		$canDo = \JHelperContent::getActions('com_content', 'category', $this->filter['category_id']);

		\JToolbarHelper::title(\JText::_('COM_CONTENT_FEATURED_TITLE'), 'star featured');

		if ($canDo->get('core.create'))
		{
			\JToolbarHelper::addNew('article.add');
		}

		if ($canDo->get('core.edit.state'))
		{
			\JToolbarHelper::publish('articles.publish', 'JTOOLBAR_PUBLISH', true);
			\JToolbarHelper::unpublish('articles.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			\JToolbarHelper::custom('articles.unfeatured', 'unfeatured.png', 'featured_f2.png', 'JUNFEATURE', true);
			\JToolbarHelper::archiveList('articles.archive');
			\JToolbarHelper::checkin('articles.checkin');
		}

		if ($this->filter['published']== -2 && $canDo->get('core.delete'))
		{
			\JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'articles.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			\JToolbarHelper::trash('articles.trash');
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			\JToolbarHelper::preferences('com_content');
		}

		\JToolbarHelper::help('JHELP_CONTENT_FEATURED_ARTICLES');
	}
}
