<?php
/**
 * Vista: widget-inline.tpl.php
 *
 * Variables del módulo:
 * - $label, $header, $cta, $message
 * - $phone, $agent_image
 * - $position ('left'|'right'), $openDefault (int), $assetsUrl
 */

$esc = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

// Normaliza teléfono a dígitos (E.164 sin símbolos)
$phoneDigits = preg_replace('/\D+/', '', (string)($phone ?? ''));

// Posición
$posClass  = ($position === 'left') ? 'wcs_fixed_left' : 'wcs_fixed_right';
$openClass = !empty($openDefault) ? ' wcs-show' : '';

// Imagen (fallback)
$img = (string)($agent_image ?? '');
if ($img === '') {
  $img = rtrim((string)$assetsUrl, '/').'/img/person_default.jpg';
}
?>
<div class="whatsapp_chat_support <?= $posClass . $openClass; ?>" id="WhatsappWidget" data-widget="inline">

  <!-- Botón flotante -->
  <button
    type="button"
    class="wcs_button wcs_button_circle"
    aria-haspopup="dialog"
    aria-controls="wcs_popup"
    aria-expanded="<?= !empty($openDefault) ? 'true' : 'false'; ?>"
  >
    <span class="fa-brands fa-whatsapp" aria-hidden="true"></span>
    <span class="visually-hidden">Open WhatsApp chat</span>
  </button>

  <!-- Etiqueta -->
  <div class="wcs_button_label">
    <?= $esc($label); ?>
  </div>

  <!-- Popup -->
  <div class="wcs_popup" id="wcs_popup" role="dialog" aria-modal="false" aria-label="WhatsApp chat">
    <button type="button" class="wcs_popup_close" aria-label="Close">
      <span class="fa-solid fa-xmark" aria-hidden="true"></span>
    </button>

    <header class="wcs_popup_header">
      <span class="fa-brands fa-whatsapp" aria-hidden="true"></span>
      <strong><?= $esc($header); ?></strong>
      <div class="wcs_popup_header_description"><?= $esc($cta); ?></div>
    </header>

    <!-- Input: Single person -->
    <div class="wcs_popup_input" data-number="<?= $esc($phoneDigits); ?>">
      <input type="text" placeholder="<?= $esc($message); ?>" />
      <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
      <span class="visually-hidden">Send message</span>
    </div>

    <figure class="wcs_popup_avatar">
      <img src="<?= $esc($img); ?>" alt="Support avatar">
    </figure>
  </div>
</div>
