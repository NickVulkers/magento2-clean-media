# Overview
*Original [module](https://github.com/sivaschenko/magento2-clean-media) by [Sergii Ivaschenko](https://github.com/sivaschenko).*

---
The module provides a command for retrieving information about catalog media files.
```
bin/magento nv:catalog:media

Media Gallery entries: 17996.
Files in directory: 23717.
Cached images: 353597.
Unused files: 5847.
Missing files: 4.
Duplicated files: 157.
```

The following options include more details in the output:
- list all unused files with the `-u` option
- list all files referenced in the database but missing in the filesystem with the `-m` option
- list all duplicated files with the `-d` option

Also, it allows to clean up the filesystem and db:
- remove unused files with the `-r` option
- remove database rows referencing non-existing files with the `-o` option
- remove duplicated files and replace references in the database with the `-x` option
---
## Index
- [Installation](#installation)
- [Usage](#installation)
  - [Information about media](#information-about-media)
  - [List missing files](#list-missing-files)
  - [List unused files](#list-unused-files)
  - [Remove unused files](#remove-unused-files)
  - [List duplicated files](#list-duplicated-files)
  - [Remove duplicated files](#remove-duplicated-files)
---
## Installation
Run the following commands from the project root directory:
```
composer require nickvulkers/magento2-clean-media
bin/magento module:enable NickVulkers_CleanMedia
bin/magento setup:upgrade
```

## Usage
### Information about media
```
bin/magento nv:catalog:media

Media Gallery entries: 17996.
Files in directory: 23717.
Cached images: 353597.
Unused files: 5847.
Missing files: 4.
Duplicated files: 1.
```

### List missing files
```
bin/magento nv:catalog:media -m

Missing media files:
/i/m/image1.jpg
/i/m/image2.jpg
/i/m/image3.jpg
/i/m/image4.jpg

Media Gallery entries: 17996.
Files in directory: 23717.
Cached images: 353597.
Unused files: 5847.
Missing files: 4.
Duplicated files: 1.
```

### List unused files
```
bin/magento nv:catalog:media -u

Unused file: /i/m/image5847.jpg
...

Media Gallery entries: 17996.
Files in directory: 23717.
Cached images: 353597.
Unused files: 5847.
Missing files: 4.
Duplicated files: 1.
```

### Remove unused files
```
bin/magento nv:catalog:media -r

Unused "/m/i/mixer.glb" was removed
```

### List duplicated files
```
bin/magento nv:catalog:media -m

Duplicate "/i/m/image5847.jpg" to "/i/m/image5007.jpg"

Media Gallery entries: 17996.
Files in directory: 23717.
Cached images: 353597.
Unused files: 5847.
Missing files: 4.
Duplicated files: 1.

Removed unused files: 1.
Disk space freed: 1 Mb
```

### Remove duplicated files
```
bin/magento nv:catalog:media -x

Duplicate "/p/u/pub_1.jpg" was removed

Media Gallery entries: 2.
Files in directory: 4.
Cached images: 189.
Unused files: 2.
Missing files: 0.
Duplicated files: 1.

Removed duplicated files: 1.
Updated catalog_product_entity_varchar rows: 1
Updated catalog_product_entity_media_gallery rows: 1
Disk space freed: 1 Mb
```