# cmsimple-XHCL-1.7.2 - beta 1

(I will also publish a clean 1.7.1 beta)

* This is a clean modified  Fork of Cmsimple XH 1.7.2 with clean urls and a http - security filter.

* It needs php 5.4+ (not php 5.3 !)  and is tested with php 7.4 - It should not be used in windows (Xampp etc.)

* You may not use ( , ) , [ or ] in links !

* Bug : The 'lost password' - recovery has no function - The reason is the filter (?&function=forgotten ) which is cleaned to &function=forgotten ( will be rewritten).

You can  in this case take the top 2 lines of the config.php of the download-version and exchange them with the top of the config.php. Then You have again the password 'test'.

* This version has an integrated full-backup from the Cmsimple_XH - forum ( start with : ...de/?backup ) which appears after a while under userfiles (filebrowser).

* The final version will have a different admin-ui and other templates

