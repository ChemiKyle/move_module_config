$(function() {
    var selectedModulePrefix;
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

    function makeAjaxCall(prefix = null, sourceProjectId = null, targetProjectId = null, transfer = null) {
        $.get(ajax_page,
                {
                    ext_prefix: prefix,
                    source_project_id: sourceProjectId,
                    target_project_id: targetProjectId,
                    transfer: transfer
                },
                function(data) {
                    if (!transfer) {
                        if (prefix && !sourceProjectId) {
                            //populateProjects(null, $targetProjectsDropdown);
                            sourceProjectOptions = [];
                            for (project of JSON.parse(data)) {
                                sourceProjectOptions.push(`<option value="${project.project_id}">${project.name}</option>`);
                            }
                            populateProjects(sourceProjectOptions, $sourceProjectsDropdown);
                            populateProjects(sourceProjectOptions, $targetProjectsDropdown);
                        } else if (prefix && sourceProjectId) {
                            var targetProjectOptions = $sourceProjectsDropdown
                                .clone()
                                .find('option')
                                .not("[value='']")
                                .not(`[value='${sourceProjectId}']`);

                            populateProjects(targetProjectOptions, $targetProjectsDropdown);
                            moduleConfig = JSON.parse(data);
                        }
                    } else {
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

        makeAjaxCall(selectedModulePrefix, selectedSourceProjectId, selectedTargetProjectId, true);

        });

    $('#dump-text').click(function() {
            if (!moduleConfig) {
                alert("You must select a module and a project.");
                return;
            }
            $('#text-dump-area').text(JSON.stringify(moduleConfig));
            });

    $('#download-settings').click(function() {
            // encode module's configuration as raw text data tied to a link
            // see https://stackoverflow.com/questions/3665115/how-to-create-a-file-in-memory-for-user-to-download-but-not-through-server
            // for more options
            const filename = `${selectedModulePrefix}_${selectedSourceProjectId}.json`;
            let element = document.createElement('a');
            element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(JSON.stringify(moduleConfig)));
            element.setAttribute('download', filename);
            element.style.display = 'none';
            document.body.appendChild(element);
            element.click();
            document.body.removeChild(element);
    });

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

