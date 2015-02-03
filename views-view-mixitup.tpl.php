<?php
/**
 * @file
 * Default view template to display content with MixItUp.
 */
?>

<?php if (isset($filters)): ?>
  <div class="filters_wrapper"><?php print $filters; ?></div>
<?php endif; ?>

<?php foreach ($rows as $id => $row): ?>
  <div class="mix_item mix<?php if ($classes_array[$id]) print ' ' . $classes_array[$id]; ?> <?php print ' ' . $view->result[$id]->classes; ?>">
    <?php print $row; ?>
  </div>
<?php endforeach; ?>
