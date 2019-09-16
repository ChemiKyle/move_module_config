<?php

require_once( 'ExternalModule.php' );

use \ExternalModules\ExternalModules;

$source_project_id = ($_GET['source_project_id']) ?: $_GET['pid']; // fall back to pid if not set
$ext_prefix = $_GET['ext_prefix'];
$target_project_id = $_GET['target_project_id'];
$transfer = $_GET['transfer'];
$use_file = $_GET['use_file'];
$event_fields = $_GET['event_fields'];
$json_string = $_GET['json_string'];

if (!$target_project_id) {
    if ($ext_prefix) {
        if (!$source_project_id) {
            // returning a list of projects for a specific module on the control center
            $enabled_projects = \ExternalModules\ExternalModules::getEnabledProjects($ext_prefix)->fetch_all(MYSQLI_ASSOC);
            $module_config_schema = \ExternalModules\ExternalModules::getConfig($ext_prefix);
            echo json_encode( ['enabledProjects' => $enabled_projects, 'moduleConfigSchema' => $module_config_schema] );
        } else {
            // return a module's settings for a specified project
            $source_config = \ExternalModules\ExternalModules::getProjectSettingsAsArray($ext_prefix, $source_project_id);
            if (!$use_file) {
                echo json_encode( $source_config );
                return;
            } else {
                // export module settings as JSON
                $source_to_target = [];
                $sql = \EMCC\ExternalModule\ExternalModule::mapEventIdsToNames($source_project_id);
                $response = \ExternalModules\ExternalModules::query($sql);
                while ($row = $response->fetch_row()) {
                    $source_to_target[$row[0]] = $row[1];
                }
                convertEventFields($ext_prefix, $source_config, $event_fields, $source_to_target, $use_file);
            }
        }
    } else {
        echo json_encode(\ExternalModules\ExternalModules::getEnabledModules($_GET['pid']));
    }
} else if ($target_project_id && $transfer) {
    $source_to_target = [];
    if ($source_project_id){
        // internal transfer
        $source_config = \ExternalModules\ExternalModules::getProjectSettingsAsArray($ext_prefix, $source_project_id);
        $sql = \EMCC\ExternalModule\ExternalModule::mapSourceEventIdToTarget($source_project_id, $target_project_id);
    } else {
        // import a JSON file
        //$check_source_config = \ExternalModules\ExternalModules::getProjectSettingsAsArray($ext_prefix, $target_project_id);
        $source_config = $json_string;
        $sql = \EMCC\ExternalModule\ExternalModule::mapEventNamesToIds($target_project_id);
    }
    if ($event_fields !== "") {
        $response = \ExternalModules\ExternalModules::query($sql);

        while ($row = $response->fetch_row()) {
            $source_to_target[$row[0]] = $row[1];
        }
    }
    convertEventFields($ext_prefix, $source_config, $event_fields, $source_to_target, $use_file = FALSE, $target_project_id);
}


function convertEventFields($ext_prefix, $source_config, $event_fields, $source_to_target, $use_file = FALSE, $target_project_id = NULL) {
    foreach($source_config as $key => &$value) {
        if (array_key_exists('system_value', $value)) {
            // skip system level values
            continue;
        }

        // check valid keys for values that are keys for lookup tables
        // e.g. FRSL stores event ID as opposed to name, making a transfer useless
        if (in_array($key, $event_fields)) {
            // replace event source value with target equivalent
            foreach($source_to_target as $source_event => $target_event) {
                // json encode and decode to flatten nested arrays during replacement
                $value['value'] = json_decode( str_replace("\"$source_event\"", "\"$target_event\"", json_encode($value['value'])) );
            }
        }
        if (!$use_file) {
            \ExternalModules\ExternalModules::setProjectSetting($ext_prefix, $target_project_id, $key, $value['value']);
        }
    }

    if ($use_file) {
        echo htmlspecialchars(json_encode($source_config), ENT_HTML5, 'UTF-8');
    }
}
?>
