<?php

namespace EMCC\ExternalModule;

use ExternalModules;
use ExternalModules\AbstractExternalModule;
use REDCap;

class ExternalModule extends AbstractExternalModule {

    function collectModules() {
        // fetch module id to name mappings
        // Note: The results also contain disabled modules (modules disabled at the control center level and not at the project level)
        $sql = "SELECT DISTINCT em.* FROM redcap_external_modules em INNER JOIN
            (SELECT * FROM redcap_external_module_settings
                WHERE `key` = 'enabled'
                AND value = 'true'
                AND project_id IS NOT NULL  -- TODO: support system level config export in control center
            ) settings
            ON em.external_module_id = settings.external_module_id;";
        $result = $this->framework->query($sql, []);
        $module_mapping = [];

        while ($row = $result->fetch_assoc()) {
            $module_mapping[$row['directory_prefix']] = $row['external_module_id'];
        }
        return $module_mapping;
    }

    function collectModuleSettings($external_module_id = NULL, $project_id = NULL) {
        $query = $this->framework->createQuery();
        $query->add("SELECT * FROM redcap_external_module_settings", []);
        if ($external_module_id) {
            $query->add("and external_module_id = ?", [$external_module_id]);
        }
        if ($project_id) {
            $query->add("and project_id = ?", [$project_id]);
        }
        $result = $query->execute();

        while ($row = $result->fetch_assoc()) {
            $module_settings[$row['key']] = $row['value'];
        }
        return $module_settings;
    }

    function getEnabledModules($prefix) {
        \ExternalModules\ExternalModules::getConfig($prefix);
        return "not implemented";
    }

    public function mapSourceEventIdToTarget($source_event_id, $target_event_id) {
        $sql = "SELECT A.event_id, B.event_id
            FROM redcap_events_metadata AS A
                INNER JOIN redcap_events_arms AS EAA ON (A.arm_id = EAA.arm_id)
                INNER JOIN redcap_events_metadata AS B ON (A.descrip = B.descrip)
                INNER JOIN redcap_events_arms AS EAB ON (B.arm_id = EAB.arm_id)
                WHERE
                    EAB.project_id = ?
                    AND
                    EAA.project_id = ?";
        $result = $this->framework->query($sql, [$target_event_id, $source_event_id]);
        return($result);
    }

    public function mapEventIdsToNames($project_id) {
        $sql = "SELECT A.event_id, A.descrip FROM redcap_events_metadata as A
                    INNER JOIN redcap_events_arms as B ON (A.arm_id = B.arm_id)
                    WHERE B.project_id = ?
                    ;";
        $result = $this->framework->query($sql, [$project_id]);
        return($result);
    }

    public function mapEventNamesToIds($project_id) {
        $sql = "SELECT A.descrip, A.event_id FROM redcap_events_metadata as A
                    INNER JOIN redcap_events_arms as B ON (A.arm_id = B.arm_id)
                    WHERE B.project_id = ?
                    ;";
        $result = $this->framework->query($sql, [$project_id]);
        return($result);
    }
}
