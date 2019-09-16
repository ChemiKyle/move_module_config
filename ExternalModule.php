<?php

namespace EMCC\ExternalModule;

use ExternalModules;
use ExternalModules\AbstractExternalModule;
use REDCap;

class ExternalModule extends AbstractExternalModule {

    function collectModules() {
        // fetch module id to name mappings
        $sql = "SELECT * FROM redcap_external_modules;";
        $result = $this->framework->query($sql);
        $module_mapping = [];

        while ($row = db_fetch_assoc($result)) {
            $module_mapping[$row['directory_prefix']] = $row['external_module_id'];
        }
        return $module_mapping;
    }

    function collectModuleSettings($external_module_id = NULL, $project_id = NULL) {
        $sql = "SELECT * FROM redcap_external_module_settings
        " . (($external_module_id) ? " WHERE external_module_id = '" . $external_module_id . "'" : "") . "
        " . (($project_id) ? " AND project_id = '" . $project_id . "'" : "") . ";";

        $result = $this->framework->query($sql);
        $module_settings = [];

        while ($row = db_fetch_assoc($result)) {
            $module_settings[$row['key']] = $row['value'];
        }
        return $module_settings;
    }

    function getEnabledModules($prefix) {
        \ExternalModules\ExternalModules::getConfig($prefix);
        return "not implemented";
    }

    public static function mapSourceEventIdToTarget($source_event_id, $target_event_id) {
        $sql = "SELECT A.event_id, B.event_id
            FROM redcap_events_metadata AS A
                INNER JOIN redcap_events_arms AS EAA ON (A.arm_id = EAA.arm_id)
                INNER JOIN redcap_events_metadata AS B ON (A.descrip = B.descrip)
                INNER JOIN redcap_events_arms AS EAB ON (B.arm_id = EAB.arm_id)
                WHERE
                    EAB.project_id = $target_event_id
                    AND
                    EAA.project_id = $source_event_id;";
        return ($sql);
    }

    public static function mapEventIdsToNames($project_id) {
        $sql = "SELECT A.event_id, A.descrip FROM redcap_events_metadata as A
                    INNER JOIN redcap_events_arms as B ON (A.arm_id = B.arm_id)
                    WHERE B.project_id = $project_id
                    ;";
        return($sql);
    }

    public static function mapEventNamesToIds($project_id) {
        $sql = "SELECT A.descrip, A.event_id FROM redcap_events_metadata as A
                    INNER JOIN redcap_events_arms as B ON (A.arm_id = B.arm_id)
                    WHERE B.project_id = $project_id
                    ;";
        return($sql);
    }
}
