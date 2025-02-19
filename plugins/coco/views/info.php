<?php

use Coco\Infra\View;

if (!defined("CMSIMPLE_XH_VERSION")) {header("HTTP/1.0 403 Forbidden"); exit;}

/**
 * @var View $this
 * @var string $logo
 * @var string $version
 * @var array<array{state:string,key:string,arg:string,state_key:string}> $checks
 */
?>
<!-- coco info -->
<h1>Coco <?=$version?></h1>
<div class="coco_syscheck">
  <h2><?= $this->text('syscheck_title') ?></h2>
  <?php foreach ($checks as $check): ?>
    <p class="xh_<?= $check['state'] ?? '' ?>">
      <?= $this->text((string)($check['key'] ?? ''), $check['arg'] ?? null) ?>
      <?= $this->text((string)($check['state_key'] ?? '')) ?>
    </p>
  <?php endforeach; ?>
</div>
