## Installation

### Install UdfEditor repository plugin
Start at your ILIAS root directory 
```bash
mkdir -p Customizing/global/plugins/Services/Repository/RepositoryObject
cd Customizing/global/plugins/Services/Repository/RepositoryObject
git clone https://github.com/fluxapps/UdfEditor.git UdfEditor
```

#### ILIAS 7 core ilCtrl patch
For make this plugin work with ilCtrl in ILIAS 7, you may need to patch the core, before you update the plugin (At your own risk)

Start at the plugin directory

./vendor/srag/dic/bin/ilias7_core_apply_ilctrl_patch.sh

### Install CascadingSelect plugin (optional)
Start at your ILIAS root directory
```bash
mkdir -p Customizing/global/plugins/Services/User/UDFDefinition
cd Customizing/global/plugins/Services/User/UDFDefinition
git clone https://github.com/leifos-gmbh/CascadingSelect.git CascadingSelect
```

## Contributing :purple_heart:
Please ...
1. ... register an account at https://git.fluxlabs.ch
2. ... create pull requests :fire:

## Adjustment suggestions / bug reporting :feet:
Please ...
1. ... register an account at https://git.fluxlabs.ch
2. ... ask us for a Service Level Agreement: support@fluxlabs.ch :kissing_heart:
3. ... Read and create issues
