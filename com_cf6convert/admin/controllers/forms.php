<?php
/**
 * @package     CF6Convert
 * @subpackage  com_cf6convert
 *
 * Forms controller - handles conversion actions
 */

defined('_JEXEC') or die;

class Cf6convertControllerForms extends JControllerLegacy
{
    /**
     * Convert selected Chronoforms to Convert Forms
     */
    public function convert()
    {
        // Check for request forgeries
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        $input = $app->input;
        $cid = $input->get('cid', array(), 'array');

        if (empty($cid)) {
            $app->enqueueMessage(JText::_('COM_CF6CONVERT_NO_FORMS_SELECTED'), 'warning');
            $this->setRedirect(JRoute::_('index.php?option=com_cf6convert&view=forms', false));
            return;
        }

        // Get the model
        $model = $this->getModel('Forms');

        $converted = 0;
        $errors = array();

        foreach ($cid as $id) {
            try {
                $result = $model->convertForm((int)$id);
                if ($result) {
                    $converted++;
                } else {
                    $errors[] = "Form ID $id: Conversion failed";
                }
            } catch (Exception $e) {
                $errors[] = "Form ID $id: " . $e->getMessage();
            }
        }

        // Set messages
        if ($converted > 0) {
            $app->enqueueMessage(JText::sprintf('COM_CF6CONVERT_FORMS_CONVERTED', $converted), 'success');
        }

        foreach ($errors as $error) {
            $app->enqueueMessage($error, 'error');
        }

        $this->setRedirect(JRoute::_('index.php?option=com_cf6convert&view=forms', false));
    }

    /**
     * Preview a form conversion without importing
     */
    public function preview()
    {
        $app = JFactory::getApplication();
        $input = $app->input;
        $id = $input->getInt('id', 0);

        if (!$id) {
            $app->enqueueMessage(JText::_('COM_CF6CONVERT_NO_FORM_SELECTED'), 'warning');
            $this->setRedirect(JRoute::_('index.php?option=com_cf6convert&view=forms', false));
            return;
        }

        // Get the model and generate preview
        $model = $this->getModel('Forms');
        $preview = $model->previewConversion($id);

        if ($preview) {
            // Store in session for display
            $app->setUserState('com_cf6convert.preview', $preview);
            $app->setUserState('com_cf6convert.preview_id', $id);
        }

        $this->setRedirect(JRoute::_('index.php?option=com_cf6convert&view=forms&layout=preview&id=' . $id, false));
    }

    /**
     * Get the model
     */
    public function getModel($name = 'Forms', $prefix = 'Cf6convertModel', $config = array())
    {
        return parent::getModel($name, $prefix, $config);
    }
}
