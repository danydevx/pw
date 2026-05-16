<?php
/**
 * Vista: widget-agent.tpl.php
 * Variables disponibles (desde el módulo):
 * - $label, $header, $cta
 * - $phone, $message
 * - $agent_name, $agent_status, $agent_description, $agent_image
 * - $position ('left'|'right'), $openDefault (int), $assetsUrl
 */

$esc = function($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
$phoneDigits = preg_replace('/\D+/', '', (string)$phone);
$posClass = ($position === 'left') ? 'wcs_fixed_left' : 'wcs_fixed_right';
$openClass = !empty($openDefault) ? ' wcs-show' : '';
?>
<div class="whatsapp_chat_support <?= $posClass . $openClass; ?>" id="WhatsappWidget" data-widget="agent">

  <!-- Etiqueta flotante -->
  <div class="wcs_button_label">
    <?= $esc($label); ?>
  </div>

  <!-- Botón flotante -->
  <button type="button" class="wcs_button wcs_button_circle" aria-haspopup="dialog" aria-controls="wcs_popup" aria-expanded="<?= !empty($openDefault) ? 'true' : 'false'; ?>">
    <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
    <span class="visually-hidden">Abrir chat de WhatsApp</span>
  </button>

  <!-- Popup -->
  <div class="wcs_popup" id="wcs_popup" role="dialog" aria-modal="false" aria-label="Soporte por WhatsApp">
    <button type="button" class="wcs_popup_close" aria-label="Cerrar">
      <span class="fa-solid fa-xmark" aria-hidden="true"></span>
    </button>

    <header class="wcs_popup_header">
      <strong><?= $esc($header); ?></strong><br>
      <div class="wcs_popup_header_description"><?= $esc($cta); ?></div>
    </header>

    <!-- Contenedor de personas (multi-agente compatible con el plugin) -->
    <div class="wcs_popup_person_container">
      <article
        class="wcs_popup_person"
        data-number="<?= $esc($phoneDigits); ?>"
        data-default-msg="<?= $esc($message); ?>"
        >
        <figure class="wcs_popup_person_img">
          <img src="<?= $esc($agent_image); ?>" alt="<?= $esc($agent_name); ?>">
        </figure>
        <div class="wcs_popup_person_content">
          <div class="wcs_popup_person_name"><?= $esc($agent_name); ?></div>
          <div class="wcs_popup_person_description"><?= $esc($agent_description); ?></div>
          <div class="wcs_popup_person_status"><?= $esc($agent_status); ?></div>
        </div>
      </article>

      <?php /* 
      // Si en el futuro agregas más agentes desde config,
      // replica el <article> modificando data-number, data-default-msg e imagen/nombre/estatus.
      */ ?>
    </div>
  </div>
</div>
