# External Module Config Copy

Allow privileged users to move external module configuration settings between projects, including projects on different REDCap instances by exporting configuration settings as a file.

## Prerequisites
 - REDcap >= 9.10.0

<!-- ## Easy installation -->
<!-- - Install the _External Module Config Copy_ module from the Consortium [REDCap Repo](https://redcap.vanderbilt.edu/consortium/modules/index.php) from the control center. -->

## Manual Installation
- Clone this repo into to `<redcap-root>/modules/external_module_config_copy_v0.0.0`.

## Introduction

When cloning a project within a REDCap instance, module configuration settings are carried over to the new project. You may export the project metadata, but this does not include the setting for external modules, making it impossible to transfer these between REDCap instances. This module enables this functionality.


## Use

Navigate to the Control Center and select "Transfer External Module Configurations" in the "External Modules" section of the left bar.

If the module you are transferring uses event fields, the source and target project _must_ have the same event names. This should not be a problem if the target project is a clone or export of the source project.  

To begin, select your desired module from the **External Modules** menu.

Select a **Source Project**. If you are planning on moving this configuration to another instance, click Download Settings to download a JSON file with your configuration.

### Transferring Internally
Select a **Target Project** and click Transfer Configuration Internally. Your target project should now have the same configuration settings as your source project.

### Transferring Externally
_The instance you are transferring to should also have the same module version to avoid breakages._

Navigate to the REDCap instance you wish to migrate your configuration to, and go to the module page in the control center.

Select the same External Module from the **External Modules** menu. Select the intended **Target Project** and click "Upload Configuration JSON File". Select the JSON file you exported from your other REDCap instance.

