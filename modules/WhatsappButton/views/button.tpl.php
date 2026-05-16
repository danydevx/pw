<?php
$esc = function($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
$isLeft = (($position ?? 'right') === 'left');
$isFloating = (($displayMode ?? 'floating') === 'floating');
$isIconOnly = (($buttonType ?? 'full') === 'icon');
$size = in_array(($size ?? 'md'), ['sm','md','lg'], true) ? $size : 'md';
?>
<a id="WhatsappButton"
   class="wa-button"
   href="<?= $esc($waUrl); ?>"
   target="_blank"
   rel="noopener noreferrer"
   aria-label="Abrir WhatsApp"
   data-position="<?= $isLeft ? 'left' : 'right'; ?>"
   data-mode="<?= $isFloating ? 'floating' : 'inline'; ?>"
   data-type="<?= $isIconOnly ? 'icon' : 'full'; ?>"
   data-size="<?= $esc($size); ?>">
  <i class="fa-brands fa-whatsapp wa-button__icon" aria-hidden="true"></i>
  <?php if (!$isIconOnly): ?>
    <span class="wa-button__label"><?= $esc($label); ?></span>
  <?php endif; ?>
</a>
