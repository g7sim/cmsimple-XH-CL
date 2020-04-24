<?php

$rootDir = __DIR__;

if (is_readable('./cmsimple/userprelude.php')) {
    include './cmsimple/userprelude.php';
}

include('./cmsimple/cms.php');
