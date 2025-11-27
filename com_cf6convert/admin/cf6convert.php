<?php
/**
 * @package     CF6Convert
 * @subpackage  com_cf6convert
 *
 * Main entry point for CF6 to Convert Forms converter component
 */

defined('_JEXEC') or die;

// Access check
if (!JFactory::getUser()->authorise('core.manage', 'com_cf6convert')) {
    throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

// Include dependencies
jimport('joomla.application.component.controller');

// Get the controller
$controller = JControllerLegacy::getInstance('Cf6convert');

// Execute the task
$input = JFactory::getApplication()->input;
$controller->execute($input->getCmd('task'));

// Redirect if set by the controller
$controller->redirect();
