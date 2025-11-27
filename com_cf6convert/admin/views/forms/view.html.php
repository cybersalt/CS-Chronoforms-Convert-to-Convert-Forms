<?php
/**
 * @package     CF6Convert
 * @subpackage  com_cf6convert
 *
 * Forms view
 */

defined('_JEXEC') or die;

class Cf6convertViewForms extends JViewLegacy
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $chronoformsInstalled;
    protected $convertformsInstalled;

    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->chronoformsInstalled = $this->get('ChronoformsInstalled');
        $this->convertformsInstalled = $this->get('ConvertFormsInstalled');

        // Check for errors
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the toolbar
     */
    protected function addToolbar()
    {
        JToolbarHelper::title(JText::_('COM_CF6CONVERT_MANAGER'), 'cogs');

        if ($this->chronoformsInstalled && $this->convertformsInstalled) {
            JToolbarHelper::custom('forms.convert', 'upload', 'upload', 'COM_CF6CONVERT_CONVERT', true);
        }

        JToolbarHelper::preferences('com_cf6convert');
    }
}
