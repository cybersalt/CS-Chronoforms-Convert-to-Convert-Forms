<?php
/**
 * @package     CF6Convert
 * @subpackage  com_cf6convert
 *
 * Main controller
 */

defined('_JEXEC') or die;

class Cf6convertController extends JControllerLegacy
{
    /**
     * The default view
     */
    protected $default_view = 'forms';

    /**
     * Display the view
     */
    public function display($cachable = false, $urlparams = array())
    {
        $view = $this->input->get('view', 'forms');
        $this->input->set('view', $view);

        parent::display($cachable, $urlparams);

        return $this;
    }
}
