<?php

use \ExternalModules\ExternalModules;

$source_project_id = ($_GET['source_project_id']) ?: $_GET['pid']; // fall back to pid if not set
$ext_prefix = $_GET['ext_prefix'];
$target_project_id = $_GET['target_project_id'];
$transfer = $_GET['transfer'];
$uploading = $_GET['uploading'];

if (!$target_project_id) {
    if ($ext_prefix) {
        //echo "there is an EM";
        if ($source_project_id) {
            echo json_encode( \ExternalModules\ExternalModules::getProjectSettingsAsArray($ext_prefix, $source_project_id) );
        } else {
            // returning a list of projects for a specific module on the control center
            echo json_encode( \ExternalModules\ExternalModules::getEnabledProjects($ext_prefix)->fetch_all(MYSQLI_ASSOC) );
        }
    } else {
        echo json_encode(\ExternalModules\ExternalModules::getEnabledModules($_GET['pid']));
    }
} else {
    $source_config = \ExternalModules\ExternalModules::getProjectSettingsAsArray($ext_prefix, $source_project_id);
    foreach($source_config as $key =>$value) {
        \ExternalModules\ExternalModules::setProjectSetting($ext_prefix, $target_project_id, $key, $value);
    }
    echo "attempting to overwrite module settings!";
}

//echo json_encode( \ExternalModules\ExternalModules::getProjectSettingsAsArray('form_render_skip_logic', '15') );


?>


