<?php

use Coco\Infra\View;

if (!defined("CMSIMPLE_XH_VERSION")) {header("HTTP/1.0 403 Forbidden"); exit;}

/**
 * @var View $this
 * @var string $action
 * @var list<string> $cocos
 */
?>
<!-- coco administration -->
<h1>Coco – <?=$this->text('menu_main')?></h1>
<form id="coco_admin_cocos" action="<?=$action?>" method="get">
  <input type="hidden" name="selected" value="coco"/>
  <input type="hidden" name="admin" value="plugin_main"/>
  <table>
    <tr>
      <th></th>
      <th>Coco</th>
    </tr>
<?php foreach ($cocos as $coco): ?>
    <tr>
        <td><input type="checkbox" id="coco_name_<?php echo htmlspecialchars($coco, ENT_QUOTES, 'UTF-8'); ?>" name="coco_name[]" value="<?php echo htmlspecialchars($coco, ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><label for="coco_name_<?php echo htmlspecialchars($coco, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($coco, ENT_QUOTES, 'UTF-8'); ?></label></td>
    </tr>
<?php endforeach; ?>
  </table>
  <p><button name="action" value="delete"><?=$this->text('label_delete')?></button></p>
</form>
