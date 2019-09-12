$(function() {
    const $externalModules = $('#external-modules');
    const $sourceProjects = $('#source-projects');
    makeAjaxCall();

    $externalModules.change(function() {
            console.log('changed');
            const selectedModule = $(this).children('option:selected')[0];
            makeAjaxCall(selectedModule.getAttribute('prefix'));
        });


    $sourceProjects.change(function() {
        const selectedModule = $externalModules
            .children('option:selected')[0]
            .getAttribute('prefix');
        const selectedProject = $(this).children('option:selected')[0];
        const pid = selectedProject.getAttribute('value');

        makeAjaxCall(selectedModule, pid);
        });


    function makeAjaxCall(prefix = null, sourceProjectId = null, targetProjectId = null) {
    console.log(prefix);
    console.log(sourceProjectId);
        $.get(ajax_page,
                {
                    ext_prefix: prefix,
                    source_project_id: sourceProjectId,
                    target_project_id: targetProjectId
                },
                function(data) {
                    console.log("request performed");
                    console.log(data);
                    console.log(JSON.parse(data));
                    if (prefix && !sourceProjectId) {
                        const projects = JSON.parse(data);
                        populateProjects(projects, $sourceProjects);
                    } else if (prefix && sourceProjectId) {
                        const projects = JSON.parse(data)
                    }
                }
             );
         }

    function populateProjects(projects, $projectList) {
        $projectList.find('option').remove();
        $projectList.append('<option value="" disabled selected hidden>Please select a project...</option>');
        for (project of projects) {
            console.log(project);
            $projectList.append(`<option value="${project.project_id}">${project.name}</option>`);
        }
    }

});

