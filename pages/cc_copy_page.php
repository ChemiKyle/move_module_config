<?php

require_once dirname(__DIR__) . '/ExternalModule.php';
require_once APP_PATH_DOCROOT . 'ControlCenter/header.php';

$title = REDCap::escapeHtml('Copy modules page');
echo RCView::h4([], $title);
echo 'Hello World!';

$E = new \EMCC\ExternalModule\ExternalModule();

$module_mapping = $E->collectModules();
$module_settings = $E->collectModuleSettings();

//\EMCC\ExternalModule\ExternalModule::collectModules();
print_r("<pre>");
print_r("Module mappings:\n");
var_dump($module_mapping);
print_r("Module settigns:\n");
var_dump($module_settings);
print_r("</pre>");

//export as JSON; placeholders for Module_id and for Project_id
//load in JSON

?>

<!-- TODO: tables here -->

<?php require_once APP_PATH_DOCROOT . 'ControlCenter/footer.php'; ?>
