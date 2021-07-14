<?php

$source_project_id = ($_GET['source_project_id']) ?: $_GET['pid']; // fall back to pid if not set
$ext_prefix = $_GET['ext_prefix'];
$target_project_id = $_GET['target_project_id'];
$transfer = $_GET['transfer'];
$use_file = $_GET['use_file'] == "true" ? true : false;
$event_fields = $_GET['event_fields'];
$json_string = $_GET['json_string'];

if (!$target_project_id) {
    // config exports
    if ($ext_prefix) {
        if (!$source_project_id) {
            // returning a list of projects for a specific module on the control center
            // recreates: \ExternalModules\ExternalModules::getEnabledProjects($ext_prefix);
            $sql = "SELECT s.project_id, p.app_title as name
                            FROM redcap_external_modules m
                            JOIN redcap_external_module_settings s
                                ON m.external_module_id = s.external_module_id
                            JOIN redcap_projects p
                                ON s.project_id = p.project_id
                            WHERE m.directory_prefix = ?
                                and p.date_deleted IS NULL
                                and `key` = 'enabled'
                                and value = 'true'";

            $enabled_projects = [];
            $result = $module->query($sql, [$ext_prefix]);

            while ($row = $result->fetch_assoc()) {
                $enabled_projects[] = $row;
            }

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
                $response = $module->mapEventIdsToNames($source_project_id);
                while ($row = $response->fetch_row()) {
                    $source_to_target[$row[0]] = $row[1];
                }
                $converted_config = convertEventFields($ext_prefix, $source_config, $event_fields, $source_to_target, $writing_config = FALSE);
                echo htmlspecialchars(json_encode($converted_config), ENT_HTML5, 'UTF-8');
            }
        }
    } else {
        echo json_encode(\ExternalModules\ExternalModules::getEnabledModules($_GET['pid']));
    }
} else if ($target_project_id && $transfer) {
    // imports/actions that update target project's module config
    $source_to_target = [];
    if ($source_project_id){
        // internal transfer
        $source_config = \ExternalModules\ExternalModules::getProjectSettingsAsArray($ext_prefix, $source_project_id);
        $response = $module->mapSourceEventIdToTarget($source_project_id, $target_project_id);
    } else {
        // import a JSON file
        //$check_source_config = \ExternalModules\ExternalModules::getProjectSettingsAsArray($ext_prefix, $target_project_id);
        $source_config = $json_string;
        $response = $module->mapEventNamesToIds($target_project_id);
    }
    if ($event_fields !== "") {
        while ($row = $response->fetch_row()) {
            $source_to_target[$row[0]] = $row[1];
        }
    }

    $converted_config = convertEventFields($ext_prefix, $source_config, $event_fields, $source_to_target, $writing_config = !$use_file, $target_project_id);
    if ($use_file) {
        echo json_encode(\ExternalModules\ExternalModules::formatRawSettings($ext_prefix, $target_project_id, $converted_config));
    }
}


function convertEventFields($ext_prefix, $source_config, $event_fields, $source_to_target, $writing_config = FALSE, $target_project_id = NULL) {
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
        if ($writing_config) {
            // save settings as they are converted to reduce overhead
            \ExternalModules\ExternalModules::setProjectSetting($ext_prefix, $target_project_id, $key, $value['value']);
        }
    }

    if (!$writing_config) {
        return $source_config;
    }
}
?>
