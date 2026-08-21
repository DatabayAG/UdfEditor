# RepositoryObject Plugin - UdfEditor

## Requirements

| Component | Version(s)                                                                                    | Link                      |
|-----------|-----------------------------------------------------------------------------------------------|---------------------------|
| PHP       | ![](https://img.shields.io/badge/8.3-blue.svg) ![](https://img.shields.io/badge/8.4-blue.svg) | [PHP](https://php.net)    |
| ILIAS     | ![](https://img.shields.io/badge/11.x-orange.svg)                                             | [ILIAS](https://ilias.de) |

---

## Table of contents

<!-- TOC -->
* [RepositoryObject Plugin - UdfEditor](#repositoryobject-plugin---udfeditor)
  * [Requirements](#requirements)
  * [Table of contents](#table-of-contents)
  * [Installation](#installation)
    * [Install CascadingSelect plugin (optional)](#install-cascadingselect-plugin-optional)
  * [Usage](#usage)
<!-- TOC -->

---

## Installation

1. Clone this repository to **Customizing/global/plugins/Services/Repository/RepositoryObject/UdfEditor**
2. Install the Composer dependencies
   ```bash
   cd public/Customizing/global/plugins/Services/Repository/RepositoryObject/UdfEditor
   composer install --no-dev
   ```
   Developers **MUST** omit the `--no-dev` argument.

3. Login to ILIAS with an administrator account (e.g. root)
4. Select **Plugins** in **Extending ILIAS** inside the **Administration** main menu.
5. Search for the **UdfEditor** plugin in the list of plugin and choose **Install** from the **Actions** drop-down.
6. Choose **Activate** from the **Actions** dropdown.

### Install CascadingSelect plugin (optional)
Start at your ILIAS root directory
```bash
mkdir -p public/Customizing/global/plugins/Services/User/UDFDefinition
cd public/Customizing/global/plugins/Services/User/UDFDefinition
git clone https://github.com/leifos-gmbh/CascadingSelect.git CascadingSelect
```

## Usage

ToDo
