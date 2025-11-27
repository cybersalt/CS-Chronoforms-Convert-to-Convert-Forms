<?php
/**
 * @package     CF6Convert
 * @subpackage  com_cf6convert
 *
 * Forms list template
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
?>

<style>
.cf6convert-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}
.cf6convert-header h1 {
    margin: 0 0 10px 0;
    color: white;
}
.cf6convert-status {
    display: flex;
    gap: 20px;
    margin: 20px 0;
}
.cf6convert-status-box {
    padding: 15px 25px;
    border-radius: 8px;
    background: #f8f9fa;
    border-left: 4px solid #28a745;
}
.cf6convert-status-box.warning {
    border-left-color: #dc3545;
}
.cf6convert-notes {
    background: #fff3cd;
    border: 1px solid #ffc107;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}
.cf6convert-notes h4 {
    margin-top: 0;
    color: #856404;
}
.cf6convert-notes ul {
    margin-bottom: 0;
}
</style>

<div class="cf6convert-header">
    <h1>Chronoforms 6 to Convert Forms</h1>
    <p>Convert your Chronoforms to Convert Forms format and import them directly.</p>
</div>

<?php if (!$this->chronoformsInstalled): ?>
    <div class="alert alert-danger">
        <h4>Chronoforms Not Found</h4>
        <p>Chronoforms 6 does not appear to be installed on this site. This component requires Chronoforms 6 to be installed to convert forms.</p>
        <?php
        // Debug: test the actual detection logic
        $db = JFactory::getDbo();
        $tables = $db->getTableList();
        $foundTable = null;

        // Same logic as model - find table ending with chronoengine_forms6
        foreach ($tables as $table) {
            if (preg_match('/chronoengine_forms6$/i', $table)) {
                $foundTable = $table;
                break;
            }
        }

        if ($foundTable) {
            echo '<div class="alert alert-warning">';
            echo '<strong>DEBUG: Table found by regex:</strong> ' . htmlspecialchars($foundTable) . '<br>';
            echo '<strong>But $this->chronoformsInstalled = </strong>' . var_export($this->chronoformsInstalled, true) . '<br>';
            echo '<strong>This means the model method is not being called correctly or returning wrong value.</strong>';
            echo '</div>';
        }

        echo '<p><strong>Database prefix:</strong> ' . htmlspecialchars($db->getPrefix()) . '</p>';

        $relevantTables = array();
        foreach ($tables as $t) {
            if (stripos($t, 'chrono') !== false) {
                $relevantTables[] = $t;
            }
        }
        if (!empty($relevantTables)) {
            echo '<p><strong>Found chrono-related tables:</strong><br>' . implode('<br>', $relevantTables) . '</p>';
        }
        ?>
    </div>
<?php elseif (!$this->convertformsInstalled): ?>
    <div class="alert alert-danger">
        <h4>Convert Forms Not Found</h4>
        <p>Convert Forms does not appear to be installed on this site. Please install Convert Forms before converting your Chronoforms.</p>
    </div>
<?php else: ?>

<div class="cf6convert-status">
    <div class="cf6convert-status-box">
        <strong>Chronoforms 6</strong><br>
        <span style="color: #28a745;">✓ Installed</span>
    </div>
    <div class="cf6convert-status-box">
        <strong>Convert Forms</strong><br>
        <span style="color: #28a745;">✓ Installed</span>
    </div>
    <div class="cf6convert-status-box">
        <strong>Forms Found</strong><br>
        <?php echo count($this->items); ?> form(s)
    </div>
</div>

<div class="cf6convert-notes">
    <h4>Important Notes</h4>
    <ul>
        <li><strong>Email Tasks:</strong> Email notifications will be automatically converted to Convert Forms format.</li>
        <li><strong>reCAPTCHA:</strong> You'll need to configure reCAPTCHA separately in Convert Forms if your forms use it.</li>
        <li><strong>Conditional Logic:</strong> Complex conditional logic may need manual adjustment after conversion.</li>
        <li><strong>Custom PHP:</strong> Any custom PHP code will be extracted but may need modification for Convert Forms.</li>
        <li><strong>Backup:</strong> Always backup your database before converting forms!</li>
    </ul>
</div>

<form action="<?php echo JRoute::_('index.php?option=com_cf6convert&view=forms'); ?>" method="post" name="adminForm" id="adminForm">
    <div id="j-main-container">

        <?php if (empty($this->items)): ?>
            <div class="alert alert-info">
                <p>No Chronoforms found to convert.</p>
            </div>
        <?php else: ?>
            <table class="table table-striped" id="formList">
                <thead>
                    <tr>
                        <th width="1%" class="center">
                            <?php echo JHtml::_('grid.checkall'); ?>
                        </th>
                        <th width="1%" class="nowrap center">
                            Status
                        </th>
                        <th>
                            <?php echo JHtml::_('grid.sort', 'Title', 'a.title', $listDirn, $listOrder); ?>
                        </th>
                        <th width="15%">
                            Alias
                        </th>
                        <th width="10%">
                            Fields
                        </th>
                        <th width="10%">
                            Email Tasks
                        </th>
                        <th width="5%" class="nowrap center">
                            <?php echo JHtml::_('grid.sort', 'ID', 'a.id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->items as $i => $item):
                        $views = json_decode($item->views, true) ?: array();
                        $functions = json_decode($item->functions, true) ?: array();

                        // Count actual fields (not containers, html, etc.)
                        $fieldTypes = array('field_text', 'field_textarea', 'field_radios', 'field_checkbox',
                                           'field_select', 'field_hidden', 'field_button', 'field_email',
                                           'field_dropdown', 'field_password', 'field_date', 'field_number',
                                           'field_file', 'field_upload', 'field_checkboxes');
                        $fieldCount = 0;
                        foreach ($views as $view) {
                            if (isset($view['type']) && in_array($view['type'], $fieldTypes)) {
                                $fieldCount++;
                            }
                        }

                        // Count email tasks
                        $emailCount = 0;
                        foreach ($functions as $func) {
                            if (isset($func['type']) && $func['type'] === 'email') {
                                $emailCount++;
                            }
                        }
                    ?>
                        <tr class="row<?php echo $i % 2; ?>">
                            <td class="center">
                                <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                            </td>
                            <td class="center">
                                <?php if ($item->published): ?>
                                    <span class="label label-success" style="background-color: #28a745;">Published</span>
                                <?php else: ?>
                                    <span class="label label-important" style="background-color: #dc3545;">Unpublished</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo $this->escape($item->title); ?></strong>
                                <?php if ($item->description): ?>
                                    <br><small class="muted"><?php echo $this->escape(JHtml::_('string.truncate', $item->description, 100)); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <code><?php echo $this->escape($item->alias); ?></code>
                            </td>
                            <td class="center">
                                <span class="badge"><?php echo $fieldCount; ?></span>
                            </td>
                            <td class="center">
                                <?php if ($emailCount > 0): ?>
                                    <span class="badge badge-success" style="background-color: #28a745; color: #ffffff;"><?php echo $emailCount; ?></span>
                                    <br><small class="text-muted">Will be converted</small>
                                <?php else: ?>
                                    <span class="badge">0</span>
                                <?php endif; ?>
                            </td>
                            <td class="center">
                                <?php echo (int)$item->id; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php echo $this->pagination->getListFooter(); ?>
        <?php endif; ?>
    </div>

    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
    <?php echo JHtml::_('form.token'); ?>
</form>

<?php endif; ?>

<div style="margin-top: 30px; padding: 15px; background: #e9ecef; border-radius: 8px;">
    <h4>How to use:</h4>
    <ol>
        <li>Select the forms you want to convert using the checkboxes</li>
        <li>Click the "Convert" button in the toolbar</li>
        <li>The forms will be imported into Convert Forms</li>
        <li>Go to Components → Convert Forms to see your imported forms</li>
    </ol>
</div>
