<?php
/**
 * @package     CF6Convert
 * @subpackage  com_cf6convert
 *
 * Forms model - handles listing and conversion of Chronoforms
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

class Cf6convertModelForms extends JModelList
{
    /**
     * Field type mapping from Chronoforms to Convert Forms
     */
    protected $fieldTypeMap = array(
        'field_text' => 'text',
        'field_textarea' => 'textarea',
        'field_email' => 'email',
        'field_hidden' => 'hidden',
        'field_radios' => 'radio',
        'field_checkbox' => 'checkbox',
        'field_checkboxes' => 'checkbox',
        'field_select' => 'dropdown',
        'field_dropdown' => 'dropdown',
        'field_button' => 'submit',
        'field_password' => 'password',
        'field_date' => 'datetime',
        'field_number' => 'number',
        'field_file' => 'fileupload',
        'field_upload' => 'fileupload',
    );

    /**
     * Elements to skip (layout elements, non-field elements)
     */
    protected $skipTypes = array(
        'area_container',
        'area_fields',
        'area_message',
        'html',
        'css',
        'google_recaptcha3',
    );

    /**
     * Constructor
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'title', 'a.title',
                'published', 'a.published',
            );
        }

        parent::__construct($config);
    }

    /**
     * Get list query for Chronoforms
     */
    protected function getListQuery()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Get the actual table name
        $cfTable = $this->getChronoformsTableName();

        if (!$cfTable) {
            // Return empty query if table doesn't exist
            $query->select('1 as id, "No Chronoforms table found" as title, 0 as published')
                  ->where('1=0');
            return $query;
        }

        $query->select('a.id, a.title, a.alias, a.published, a.description, a.params, a.views, a.functions')
              ->from($db->quoteName($cfTable, 'a'));

        // Filter by search
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $db->quote('%' . $db->escape($search, true) . '%');
            $query->where('(a.title LIKE ' . $search . ' OR a.alias LIKE ' . $search . ')');
        }

        // Add ordering
        $orderCol = $this->state->get('list.ordering', 'a.title');
        $orderDirn = $this->state->get('list.direction', 'ASC');
        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }

    /**
     * Check if Chronoforms is installed
     * Method name must be getXxx for Joomla's $this->get('Xxx') to work
     */
    public function getChronoformsInstalled()
    {
        return $this->getChronoformsTableName() !== null;
    }

    /**
     * Get the actual Chronoforms table name using the configured database prefix
     */
    public function getChronoformsTableName()
    {
        $db = $this->getDbo();
        $prefix = $db->getPrefix();
        $tables = $db->getTableList();

        // Check for table with configured prefix (most likely)
        $possibleTables = array(
            $prefix . 'chronoengine_forms6',    // ChronoForms 6 / ChronoEngine
            $prefix . 'chronoforms6_forms',     // Alternative naming
            $prefix . 'chronoforms_forms',      // Legacy
        );

        foreach ($possibleTables as $table) {
            if (in_array($table, $tables)) {
                return $table;
            }
        }

        return null;
    }

    /**
     * Check if Convert Forms is installed
     * Method name must be getXxx for Joomla's $this->get('Xxx') to work
     */
    public function getConvertFormsInstalled()
    {
        $db = $this->getDbo();
        $prefix = $db->getPrefix();
        $tables = $db->getTableList();

        // Check for convertforms table with configured prefix
        return in_array($prefix . 'convertforms', $tables);
    }

    /**
     * Get a single Chronoform by ID
     */
    public function getChronoform($id)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $cfTable = $this->getChronoformsTableName();
        if (!$cfTable) {
            return null;
        }

        $query->select('*')
              ->from($db->quoteName($cfTable))
              ->where($db->quoteName('id') . ' = ' . (int)$id);

        $db->setQuery($query);
        return $db->loadObject();
    }

    /**
     * Preview a form conversion without actually importing
     */
    public function previewConversion($id)
    {
        $chronoform = $this->getChronoform($id);

        if (!$chronoform) {
            return false;
        }

        return $this->buildConvertFormsStructure($chronoform);
    }

    /**
     * Convert a Chronoform and import it into Convert Forms
     */
    public function convertForm($id)
    {
        $chronoform = $this->getChronoform($id);

        if (!$chronoform) {
            throw new Exception('Chronoform not found with ID: ' . $id);
        }

        // Build the Convert Forms structure
        $convertData = $this->buildConvertFormsStructure($chronoform);

        // Insert into Convert Forms table
        return $this->insertIntoConvertForms($convertData, $chronoform->title);
    }

    /**
     * Build Convert Forms structure from Chronoforms data
     */
    protected function buildConvertFormsStructure($chronoform)
    {
        // Parse nested JSON
        $views = json_decode($chronoform->views, true) ?: array();
        $params = json_decode($chronoform->params, true) ?: array();
        $functions = json_decode($chronoform->functions, true) ?: array();

        // Convert fields
        $fields = $this->convertFields($views);

        // Convert email tasks to Convert Forms format
        $emails = $this->convertEmailTasks($functions);

        // Build params structure for Convert Forms
        $cfParams = array(
            'fields' => $fields,
            'store' => '1',
            'loadcss' => '1',
            'loadjs' => '1',
            'classprefix' => 'cf-',
            'inputclass' => '',
            'labelclass' => '',
            'classsuffix' => '',
            'layout' => '',
            'inputcssclass' => '',
            'popup' => array(
                'enabled' => '0',
            ),
            'submitbtnlabel' => 'Submit',
            'submitbtnclass' => '',
            'successmsg' => $this->extractSuccessMessage($functions),
            'phpscripts' => $this->extractPhpScripts($functions),
            'sendnotifications' => !empty($emails) ? '1' : '0',
            'emails' => $emails,
            'save_data_to_db' => '1',
            'onsuccess' => 'msg',
            'resetform' => '1',
            'honeypot' => '1',
        );

        // Add email tasks info for notes
        $emailTasksInfo = $this->extractEmailTasks($functions);

        return array(
            'name' => $chronoform->title,
            'alias' => $chronoform->alias ?: $this->generateAlias($chronoform->title),
            'state' => (int)$chronoform->published,
            'params' => $cfParams,
            'email_tasks' => $emailTasksInfo,
            'conversion_notes' => $this->generateConversionNotes($functions),
            'original_views' => $views,
            'original_functions' => $functions
        );
    }

    /**
     * Convert Chronoforms fields to Convert Forms fields
     * Returns object with keys like fields0, fields1, etc.
     */
    protected function convertFields($views)
    {
        $fields = array();
        $fieldIndex = 0;

        foreach ($views as $viewId => $view) {
            $type = isset($view['type']) ? $view['type'] : '';

            // Skip non-field elements
            if (in_array($type, $this->skipTypes)) {
                continue;
            }

            // Skip if not a recognized field type
            if (!isset($this->fieldTypeMap[$type])) {
                continue;
            }

            $field = $this->convertField($view, $fieldIndex);
            if ($field !== null) {
                // Use fields0, fields1, etc. as keys (Convert Forms format)
                $fields['fields' . $fieldIndex] = $field;
                $fieldIndex++;
            }
        }

        return $fields;
    }

    /**
     * Convert a single field
     */
    protected function convertField($cfField, $index)
    {
        $type = isset($cfField['type']) ? $cfField['type'] : '';
        $cfType = isset($this->fieldTypeMap[$type]) ? $this->fieldTypeMap[$type] : 'text';

        // Get field name
        $params = isset($cfField['params']) ? $cfField['params'] : array();
        $name = isset($params['name']) ? $params['name'] : (isset($params['id']) ? $params['id'] : 'field_' . $index);

        // Detect email field from validation
        $validation = isset($cfField['validation']) ? $cfField['validation'] : array();
        if ($cfType === 'text' && isset($validation['email']) && $validation['email'] === 'true') {
            $cfType = 'email';
        }

        // Build field structure - key is just the index number as string
        $field = array(
            'key' => (string)$index,
            'name' => $name,
            'type' => $cfType,
            'label' => isset($cfField['label']) ? $cfField['label'] : (isset($cfField['designer_label']) ? $cfField['designer_label'] : ''),
            'description' => '',
            'required' => $this->isRequired($cfField),
            'placeholder' => isset($params['placeholder']) ? $params['placeholder'] : '',
            'hidelabel' => '0',
            'cssclass' => isset($cfField['container']['class']) ? $cfField['container']['class'] : '',
            'inputcssclass' => '',
        );

        // Add validation settings
        $field['email'] = (isset($validation['email']) && $validation['email'] === 'true') ? '1' : '0';
        $field['url'] = (isset($validation['url']) && $validation['url'] === 'true') ? '1' : '0';
        $field['integer'] = (isset($validation['integer']) && $validation['integer'] === 'true') ? '1' : '0';

        // Custom error message
        if (!empty($cfField['verror'])) {
            $field['errormessage'] = $cfField['verror'];
        }

        // Type-specific properties
        switch ($cfType) {
            case 'radio':
            case 'checkbox':
            case 'dropdown':
                $field['choices'] = $this->convertOptions(isset($cfField['options']) ? $cfField['options'] : '');
                break;

            case 'textarea':
                $field['rows'] = isset($params['rows']) ? (int)$params['rows'] : 5;
                break;

            case 'hidden':
                $field['value'] = isset($params['value']) ? $params['value'] : '';
                break;

            case 'submit':
                // Submit button has different structure in Convert Forms
                $field['text'] = isset($cfField['content']) ? $cfField['content'] : 'Submit';
                $field['align'] = 'left';
                $field['btnstyle'] = 'flat';
                $field['fontsize'] = '18';
                $field['bg'] = '#4585f4';
                $field['textcolor'] = '#ffffff';
                $field['texthovercolor'] = '#ffffff';
                $field['borderradius'] = '3';
                $field['vpadding'] = '13';
                $field['hpadding'] = '20';
                $field['size'] = 'cf-width-auto';
                // Remove fields not applicable to submit button
                unset($field['name']);
                unset($field['placeholder']);
                unset($field['required']);
                unset($field['description']);
                break;
        }

        // Input mask
        if (!empty($cfField['inputmask'])) {
            $field['inputmask'] = $cfField['inputmask'];
        }

        return $field;
    }

    /**
     * Check if field is required
     */
    protected function isRequired($cfField)
    {
        $validation = isset($cfField['validation']) ? $cfField['validation'] : array();
        $required = isset($validation['required']) ? $validation['required'] : '';
        return ($required === 'true' || $required === true) ? '1' : '0';
    }

    /**
     * Convert options string to choices structure for Convert Forms
     * Returns: array('choices' => array('1' => array('label'=>..., 'value'=>..., 'calc-value'=>''), ...))
     */
    protected function convertOptions($optionsString)
    {
        if (empty($optionsString)) {
            return array('choices' => new stdClass());
        }

        $choices = array();
        $lines = preg_split('/\r\n|\r|\n/', $optionsString);
        $index = 1;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (strpos($line, '=') !== false) {
                list($value, $label) = explode('=', $line, 2);
                $choices[(string)$index] = array(
                    'label' => trim($label),
                    'value' => trim($value),
                    'calc-value' => ''
                );
            } else {
                $choices[(string)$index] = array(
                    'label' => $line,
                    'value' => '',
                    'calc-value' => ''
                );
            }
            $index++;
        }

        return array('choices' => !empty($choices) ? $choices : new stdClass());
    }

    /**
     * Extract PHP scripts from functions
     */
    protected function extractPhpScripts($functions)
    {
        $scripts = array(
            'formprepare' => '',
            'formdisplay' => '',
            'formprocess' => '',
            'afterformsubmission' => ''
        );

        $phpCode = array();
        foreach ($functions as $func) {
            if (isset($func['type']) && $func['type'] === 'php') {
                $label = isset($func['designer_label']) ? $func['designer_label'] : (isset($func['name']) ? $func['name'] : 'Unknown');
                $phpCode[] = "// From: " . $label;
                $phpCode[] = isset($func['code']) ? $func['code'] : '';
                $phpCode[] = '';
            }
        }

        if (!empty($phpCode)) {
            $scripts['formprocess'] = implode("\n", $phpCode);
        }

        return $scripts;
    }

    /**
     * Convert email tasks to Convert Forms format
     * Returns object with emails0, emails1, etc. keys
     */
    protected function convertEmailTasks($functions)
    {
        $emails = array();
        $index = 0;

        foreach ($functions as $funcId => $func) {
            if (isset($func['type']) && $func['type'] === 'email') {
                // Convert Chronoforms placeholders to Convert Forms format
                // CF6 uses {data:fieldname} or {var:fieldname}, CF uses {field.fieldname}
                $body = isset($func['body']) ? $func['body'] : '';
                $body = $this->convertPlaceholders($body);

                $subject = isset($func['subject']) ? $func['subject'] : 'Form Submission';
                $subject = $this->convertPlaceholders($subject);

                $recipient = isset($func['recipients']) ? $func['recipients'] : '{site.email}';
                $recipient = $this->convertPlaceholders($recipient);

                $replyTo = isset($func['reply_email']) ? $func['reply_email'] : '';
                $replyTo = $this->convertPlaceholders($replyTo);

                $fromName = isset($func['from_name']) ? $func['from_name'] : '{site.name}';
                $fromName = $this->convertPlaceholders($fromName);

                $fromEmail = isset($func['from_email']) ? $func['from_email'] : '{site.email}';
                $fromEmail = $this->convertPlaceholders($fromEmail);

                // If autofields is enabled, add {all_fields} to body
                if ((isset($func['autofields']) && $func['autofields'] === '1') ||
                    (isset($func['autosubmission']) && $func['autosubmission'] === '1')) {
                    if (strpos($body, '{all_fields}') === false) {
                        $body .= "\n\n{all_fields}";
                    }
                }

                $emails['emails' . $index] = array(
                    'recipient' => $recipient,
                    'subject' => $subject,
                    'from_name' => $fromName,
                    'from_email' => $fromEmail,
                    'reply_to' => $replyTo,
                    'reply_to_name' => '',
                    'body' => $body,
                    'attachments' => '',
                );

                $index++;
            }
        }

        // Return as object (empty stdClass if no emails)
        return !empty($emails) ? $emails : new stdClass();
    }

    /**
     * Convert Chronoforms placeholders to Convert Forms format
     */
    protected function convertPlaceholders($text)
    {
        if (empty($text)) {
            return $text;
        }

        // Convert {data:fieldname} to {field.fieldname}
        $text = preg_replace('/\{data:([^}]+)\}/', '{field.$1}', $text);

        // Convert {var:fieldname} to {field.fieldname}
        $text = preg_replace('/\{var:([^}]+)\}/', '{field.$1}', $text);

        // Convert {_site_name} to {site.name}
        $text = str_replace('{_site_name}', '{site.name}', $text);

        // Convert {_site_url} to {site.url}
        $text = str_replace('{_site_url}', '{site.url}', $text);

        // Convert {_site_email} to {site.email}
        $text = str_replace('{_site_email}', '{site.email}', $text);

        // Convert {_user_name} to {user.name}
        $text = str_replace('{_user_name}', '{user.name}', $text);

        // Convert {_user_email} to {user.email}
        $text = str_replace('{_user_email}', '{user.email}', $text);

        return $text;
    }

    /**
     * Extract email tasks info for notes (original format)
     */
    protected function extractEmailTasks($functions)
    {
        $emails = array();

        foreach ($functions as $funcId => $func) {
            if (isset($func['type']) && $func['type'] === 'email') {
                $emails[] = array(
                    'name' => isset($func['designer_label']) ? $func['designer_label'] : (isset($func['name']) ? $func['name'] : 'Email Task'),
                    'enabled' => isset($func['enabled']) && $func['enabled'] === '1',
                    'recipients' => isset($func['recipients']) ? $func['recipients'] : '',
                    'subject' => isset($func['subject']) ? $func['subject'] : '',
                    'body' => isset($func['body']) ? $func['body'] : '',
                    'from_name' => isset($func['from_name']) ? $func['from_name'] : '',
                    'reply_email' => isset($func['reply_email']) ? $func['reply_email'] : '',
                    'cc' => isset($func['cc']) ? $func['cc'] : '',
                    'bcc' => isset($func['bcc']) ? $func['bcc'] : '',
                    'autofields' => isset($func['autofields']) && $func['autofields'] === '1',
                    '_event' => isset($func['_event']) ? $func['_event'] : '',
                );
            }
        }

        return $emails;
    }

    /**
     * Extract success message from functions
     */
    protected function extractSuccessMessage($functions)
    {
        foreach ($functions as $func) {
            if (isset($func['type']) && $func['type'] === 'message' &&
                isset($func['message_type']) && $func['message_type'] === 'success') {
                return isset($func['content']) ? $func['content'] : '';
            }
        }
        return 'Form submitted successfully!';
    }

    /**
     * Generate conversion notes
     */
    protected function generateConversionNotes($functions)
    {
        $notes = array();

        $emailCount = 0;
        $hasRecaptcha = false;
        $hasConditional = false;
        $hasRedirect = false;

        foreach ($functions as $func) {
            $type = isset($func['type']) ? $func['type'] : '';

            if ($type === 'email') {
                $emailCount++;
            }
            if (strpos($type, 'recaptcha') !== false) {
                $hasRecaptcha = true;
            }
            if ($type === 'if_conditions') {
                $hasConditional = true;
            }
            if ($type === 'redirect') {
                $hasRedirect = true;
            }
        }

        if ($emailCount > 0) {
            $notes[] = "Found $emailCount email action(s) - setup Convert Forms Email Task manually";
        }
        if ($hasRecaptcha) {
            $notes[] = "reCAPTCHA detected - configure in Convert Forms settings";
        }
        if ($hasConditional) {
            $notes[] = "Conditional logic found - may need manual adjustment";
        }
        if ($hasRedirect) {
            $notes[] = "Redirect action found - configure in Convert Forms";
        }

        return $notes;
    }

    /**
     * Generate alias from title
     */
    protected function generateAlias($title)
    {
        $alias = strtolower($title);
        $alias = preg_replace('/[^a-z0-9]+/', '-', $alias);
        return trim($alias, '-');
    }

    /**
     * Get the Convert Forms table name using the configured database prefix
     */
    protected function getConvertFormsTableName()
    {
        $db = $this->getDbo();
        $prefix = $db->getPrefix();

        // Return the table name using the configured prefix
        return $prefix . 'convertforms';
    }

    /**
     * Insert converted form into Convert Forms table
     * Always creates a NEW form with unique timestamp-based name
     */
    protected function insertIntoConvertForms($convertData, $originalTitle)
    {
        $db = $this->getDbo();

        // Get Convert Forms table
        $cfTable = $this->getConvertFormsTableName();

        if (!$cfTable) {
            throw new Exception('Convert Forms is not installed. Please install Convert Forms first.');
        }

        // Always create unique name with timestamp to allow multiple conversions
        $timestamp = date('Y-m-d H:i:s');
        $uniqueName = $convertData['name'] . ' (Converted ' . $timestamp . ')';

        // Prepare the record
        $record = new stdClass();
        $record->name = $uniqueName;
        $record->state = $convertData['state'];
        $record->created = JFactory::getDate()->toSql();
        $record->ordering = 0;
        $record->params = json_encode($convertData['params'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Insert the record
        $result = $db->insertObject($cfTable, $record, 'id');

        if (!$result) {
            throw new Exception('Failed to insert form into Convert Forms table');
        }

        // Log the conversion
        $this->logConversion($record->id, $originalTitle, $convertData);

        return $record->id;
    }

    /**
     * Log conversion details
     */
    protected function logConversion($newFormId, $originalTitle, $convertData)
    {
        // Store conversion details in a simple log
        $app = JFactory::getApplication();

        $logMessage = sprintf(
            'Converted Chronoform "%s" to Convert Forms ID %d. Notes: %s',
            $originalTitle,
            $newFormId,
            implode('; ', $convertData['conversion_notes'])
        );

        // Add user message about email tasks
        if (!empty($convertData['email_tasks'])) {
            $app->enqueueMessage(
                JText::sprintf('COM_CF6CONVERT_EMAIL_TASKS_NOTE', count($convertData['email_tasks'])),
                'notice'
            );
        }
    }

    /**
     * Get form count
     */
    public function getFormCount()
    {
        $cfTable = $this->getChronoformsTableName();
        if (!$cfTable) {
            return 0;
        }

        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('COUNT(*)')
              ->from($db->quoteName($cfTable));
        $db->setQuery($query);

        return (int)$db->loadResult();
    }
}
