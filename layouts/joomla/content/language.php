<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$item = $displayData;

if ($item->language === '*')
{
	echo Text::alt('JALL', 'language');
}
elseif ($item->lang->image)
{
	echo HTMLHelper::_('image', 'mod_languages/' . $item->lang->image . '.gif', '', null, true) . '&nbsp;' . htmlspecialchars($item->lang->title, ENT_COMPAT, 'UTF-8');
}
elseif ($item->lang->title)
{
	echo htmlspecialchars($item->lang->title, ENT_COMPAT, 'UTF-8');
}
else
{
	echo Text::_('JUNDEFINED');
}
