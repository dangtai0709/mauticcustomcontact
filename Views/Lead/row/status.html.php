<?php

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$class = '';
if (isset($fields['core'][$column]['value'])) {
    $class = strtolower(str_replace(' ', '_', $fields['core'][$column]['value']));
}
?>
<td>
  <?php
  if (isset($fields['core'][$column]['value'])) {
      echo "<span class='status-".$class."'>".$view->escape($fields['core'][$column]['value']).'</span>';
  }
  ?>
</td>