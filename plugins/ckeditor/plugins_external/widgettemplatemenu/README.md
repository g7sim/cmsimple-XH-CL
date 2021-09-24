CKEDITOR BOOTSTRAP GRID
---------------------
 * Introduction
 * Requirements
 * Installation
 
INTRODUCTION
------------

Rethinking the ckeditor interface to include bootstrap grid.

REQUIREMENTS
------------

This module requires the following modules:
 * This module requires a Bootstrap library in order to work properly.

INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module. Visit:
     https://www.drupal.org/docs/8/extending-drupal-8/installing-drupal-8-modules
     for further information.
     
CONFIGURATION
-------------

 * Open Administration > Configuration > Content authoring >
     Text formats and editors (admin/config/content/formats)
 * Edit a text format's settings (usually Basic HTML)
 * Drag n Drop the Add Template Menu -button to the toolbar 
     To show it to the users and drag the Insert columns buttons
 * Find and replace <div> with <div class>
   This ensures CKEditor doesn't remove the class name that the 
   bootstrap columns uses.

MAINTAINERS
-----------
 * Alexandru (lexsoft) - https://www.drupal.org/user/3487887
