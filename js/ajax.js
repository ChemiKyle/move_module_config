$(function() {
    var selectedModulePrefix;
    var selectedModuleConfigSchema;
    var selectedSourceProjectId;
    var selectedTargetProjectId;
    var moduleConfig;
    const $externalModulesDropdown = $('#external-modules');
    const $sourceProjectsDropdown = $('#source-projects');
    const $targetProjectsDropdown = $('#target-projects');
    makeAjaxCall();

    $externalModulesDropdown.change(function() {
            selectedModulePrefix = $(this).children('option:selected')[0].getAttribute('prefix');
            makeAjaxCall(selectedModulePrefix);
        });

    $sourceProjectsDropdown.change(function() {
        selectedSourceProjectId = $(this).children('option:selected')[0].getAttribute('value');
        makeAjaxCall(selectedModulePrefix, selectedSourceProjectId);
        });

    $targetProjectsDropdown.change(function() {
        selectedTargetProjectId = $(this).children('option:selected')[0].getAttribute('value');
        });

    function makeAjaxCall(prefix = null, sourceProjectId = null, targetProjectId = null, transfer = null, eventFields = null, useFile = null) {
        $.get(ajax_page,
                {
                    ext_prefix: prefix,
                    source_project_id: sourceProjectId,
                    target_project_id: targetProjectId,
                    transfer: transfer,
                    event_fields: eventFields,
                    use_file: useFile
                },
                function(data) {
                    if (!transfer && !useFile) {
                        if (prefix && !sourceProjectId) {
                            const responseData = JSON.parse(data);
                            sourceProjectOptions = [];
                            for (project of responseData.enabledProjects) {
                                sourceProjectOptions.push(`<option value="${project.project_id}">${project.name}</option>`);
                            }
                            populateProjects(sourceProjectOptions, $sourceProjectsDropdown);
                            populateProjects(sourceProjectOptions, $targetProjectsDropdown);
                            selectedModuleConfigSchema = responseData.moduleConfigSchema;
                        }
                        else if (prefix && sourceProjectId) {
                            //console.log(data);
                            var targetProjectOptions = $sourceProjectsDropdown
                                .clone()
                                .find('option')
                                .not("[value='']")
                                .not(`[value='${sourceProjectId}']`);

                            populateProjects(targetProjectOptions, $targetProjectsDropdown);
                            moduleConfig = JSON.parse(data);
                        }
                    }
                    else {
                        console.log(data);
                    }

                }
             );
         }

    $('#transfer-config').click(function() {
        // refresh variables
        selectedModulePrefix = $externalModulesDropdown.children('option:selected')[0].getAttribute('prefix');
        selectedSourceProjectId = $sourceProjectsDropdown.children('option:selected')[0].getAttribute('value');
        selectedTargetProjectId = $targetProjectsDropdown.children('option:selected')[0].getAttribute('value');

        if (selectedModulePrefix == "" ||
                selectedSourceProjectId == "" ||
                selectedTargetProjectId == "") {
                    alert("All three fields are required for an internal transfer");
                    return;
                }

        const eventFields = reportModuleEventFields(selectedModuleConfigSchema);
        makeAjaxCall(selectedModulePrefix, selectedSourceProjectId, selectedTargetProjectId, transfer = true, eventFields);

        });

    $('#dump-text').click(function() {
            if (!moduleConfig) {
                alert("You must select a module and a project.");
                return;
            }
            let eventFields = reportModuleEventFields(selectedModuleConfigSchema);
            let response;
            $.get(ajax_page,
                    {
                        ext_prefix: selectedModulePrefix,
                        source_project_id: selectedSourceProjectId,
                        transfer: false,
                        event_fields: eventFields,
                        use_file: true
                    },
                    function(data) {
                            response = JSON.parse(data);
                            $('#text-dump-area').text(JSON.stringify(response, null, 2));
                        }
                    );
            });

    $('#download-settings').click(function() {
            const eventFields = reportModuleEventFields(selectedModuleConfigSchema);
            let response;

            // cannot be wrapped in a function thanks to async
            $.get(ajax_page,
                    {
                        ext_prefix: selectedModulePrefix,
                        source_project_id: selectedSourceProjectId,
                        transfer: false,
                        event_fields: eventFields,
                        use_file: true
                    },
                    function(data) {
                            dlFile(data);
                        }
                    );

            function dlFile(fileData) {
            // encode module's configuration as raw text data tied to a link
            // see https://stackoverflow.com/questions/3665115/how-to-create-a-file-in-memory-for-user-to-download-but-not-through-server
            // for more options
                const filename = `${selectedModulePrefix}_${selectedSourceProjectId}.json`;
                let element = document.createElement('a');
                element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(fileData));
                element.setAttribute('download', filename);
                element.style.display = 'none';
                document.body.appendChild(element);
                element.click();
                document.body.removeChild(element);
            }
    });

    //TODO
    $('#upload-settings').click(function() {
        //const file = uploadFile();
        //applyFile(file, targetProjectId);
        return;
    });

    function reportModuleEventFields(moduleConfigSchema) {
        // find fields which map to events so the event_ids may be changed to match the target project
        //console.log(moduleConfigSchema['project-settings']);
        let eventFields = [];

        // must recursively search due to subsettings
        // luckily setting them only needs their key
        function eachRecursive(obj) {
            for (var k in obj)
            {
                if (typeof obj[k] == "object" && obj[k] !== null) {
                    eachRecursive(obj[k]);
                } else if (k === 'type') {
                    if (obj[k] === "event-list") {
                        eventFields.push(obj.key);
                    }
                }
            }
        }
        eachRecursive(moduleConfigSchema['project-settings']);
        return eventFields;
    }

    function populateProjects(projectOptions, $projectDropdown) {
        $projectDropdown.find('option').remove();

        if (!projectOptions) {
            $projectDropdown.append('<option value="" disabled selected hidden>Please select from the previous menu first.</option>');
            return;
        }

        $projectDropdown.append('<option value="" disabled selected hidden>Please select a project...</option>');
        $projectDropdown.append(projectOptions);
    }

});

