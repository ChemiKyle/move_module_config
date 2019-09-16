<?php

require_once dirname(__DIR__) . '/ExternalModule.php';
require_once APP_PATH_DOCROOT . 'ControlCenter/header.php';

$title = REDCap::escapeHtml('Copy modules page');
echo RCView::h4([], $title);

$E = new \EMCC\ExternalModule\ExternalModule();

$module_mapping = $E->collectModules();
$module_settings = $E->collectModuleSettings();

//\EMCC\ExternalModule\ExternalModule::collectModules();
/*
print_r("<pre>");
print_r("Module mappings:\n");
var_dump($module_mapping);
print_r("Module settigns:\n");
var_dump($module_settings);
print_r("</pre>");
*/
$ajax_page = $E->framework->getUrl('ajax.php');
$ajax_script = $E->framework->getUrl('js/ajax.js');


/*
print_r("ExternalModules");
print_r("<pre>");
var_dump(\ExternalModules\ExternalModules::getEnabledModules());
print_r("</pre>");

print_r("module_mapping");
print_r("<pre>");
var_dump($module_mapping);
print_r("</pre>");

foreach($module_mapping as $prefix => $id) {
    print_r($prefix);
    print_r("<pre>");
    print_r("\nprefix: $prefix   ; id: $id   \n");
    print_r(\ExternalModules\ExternalModules::getConfig($prefix));
    var_dump(\ExternalModules\ExternalModules::getSystemSettingsAsArray($prefix));
    print_r("\n\nProjects:\n");
    var_dump(\ExternalModules\ExternalModules::getEnabledProjects($prefix)->fetch_all(MYSQLI_ASSOC));
    print_r("</pre>");
}
*/

?>

<div class="selection-menus">
    <label for="external-modules">External Modules:</label>
    <select id="external-modules">
        <option value="" disabled selected hidden>Please select an external module...</option>
    <?php
    foreach($module_mapping as $prefix => $key) {
        echo "<option value='$key' prefix='$prefix'>" . \ExternalModules\ExternalModules::getConfig($prefix)['name'] . "</option>";
    }
    ?>
    </select>

    <div id="source-project-div">
        <label for="source-projects">Source Project:</label>
        <select id="source-projects">
            <option value="" disabled selected hidden>Choose a module first.</option>
        </select>
        <button id="download-settings">Download Settings</button>
        <button id="dump-text">Dump Settings as Text</button>
    </div>

    </br>
    <div id="target-project-div">
        <label for="target-projects">Target Project:</label>
        <select id="target-projects">
            <option value="" disabled selected hidden>Choose a module first.</option>
        </select>
        <button id="transfer-config">Transfer Configuration Internally</button>
        <input id="fileUpload" type="file" style="display:none" >
        <button id="upload-settings">Upload Configuration JSON File</button>
    </div>
</div>


<pre id="text-dump-area">
</pre>

<script>
var ajax_page = "<?php echo $ajax_page; ?>";
</script>

<?
//export as JSON; placeholders for Module_id and for Project_id
//load in JSON

/*

\ExternalModules\ExternalModules::setProjectSetting($prefix, $pid, $key, $value);

*/

?>

<!-- TODO: tables here -->

<script src="<?php echo $ajax_script; ?>"></script>



<?php require_once APP_PATH_DOCROOT . 'ControlCenter/footer.php'; ?>
