<?php
/**
 * @package     CF6Convert
 * @subpackage  com_cf6convert
 *
 * Helper class
 */

defined('_JEXEC') or die;

class Cf6convertHelper
{
    /**
     * Configure the submenu
     */
    public static function addSubmenu($vName)
    {
        JHtmlSidebar::addEntry(
            JText::_('COM_CF6CONVERT_MANAGER'),
            'index.php?option=com_cf6convert&view=forms',
            $vName == 'forms'
        );
    }

    /**
     * Get component actions for ACL
     */
    public static function getActions()
    {
        $user = JFactory::getUser();
        $result = new JObject;

        $actions = array(
            'core.admin', 'core.manage'
        );

        foreach ($actions as $action) {
            $result->set($action, $user->authorise($action, 'com_cf6convert'));
        }

        return $result;
    }
}
