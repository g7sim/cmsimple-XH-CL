# cmsimple-XH-CL-1.7.2 - beta 2

* This is a  modified  Fork of Cmsimple XH 1.7.2 with clean urls and a http - security filter.

* It has the same GPL3  license (Licenses of plugins are to be observed)

* It needs at least php 5.4+ (not php 5.3 !)  and is tested with php 7.4

* It offers both tinymce4.83 (with bootstrap plugins under configuration : flexible) and a version of ckeditor 4.14, which allows simple adding of a plugin by extraction into the plugins_external - directory (with dependencies!).

* This version has an integrated full-backup from the Cmsimple_XH - forum ( start with : ...de/backup ). The zip  appears after a while under userfiles (filebrowser) and is ready for download.

* You may not use ( ] ) [ < = or ? in links !

* It has also limitations in WINDOWS (like Xampp etc.) : a) URI-pathes like /plugins or /plugins/plugin1 (but ok is /plugins1/plugins) or /cmsimple or /templates  - which have their equivalent in the CMS-structure - are momentary not allowed in Windows - but in the Web!) ; b) The configuration of ckeditor destroys the config.php in Windows - to do manually for skin-choice - but not in the Web)

* It is not quite compatible with FHS_Adminmenu - maybe in a future version , but with hi_admin - and most of the 1.7x - plugins function.

* The final version will have a different admin-ui and other templates inklusive Bootstrap ( https://github.com/g7sim/Bootstrap3-XH  and https://github.com/g7sim/Bootstrap4-XH ) as You can find them in this git.

* If You find bugs, please make an issue (or post in the forum). You may also make a fork and a push with Your solution.


