<?php
/**
 * Vista: widget-basic.tpl.php (solo botón)
 *
 * Variables que envía el módulo:
 * - $phone, $message
 * - $agent_name, $agent_status, $agent_description, $agent_image
 * - $position ('left'|'right')
 * - $assetsUrl (para fallback de imagen)
 *
 * Opcional:
 * - $availability (array asociativo tipo ['sunday'=>'00:00-23:59', ...])
 */

$esc = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

// Teléfono en E.164 (solo dígitos)
$phoneDigits = preg_replace('/\D+/', '', (string)($phone ?? ''));

// Imagen por defecto si no viene
$img = (string)($agent_image ?? '');
if ($img === '') {
  $img = rtrim((string)$assetsUrl, '/').'/img/person_default.jpg';
}

// Disponibilidad: usa la pasada por $availability o el ejemplo de tu snippet
$availability = isset($availability) && is_array($availability) && $availability
  ? $availability
  : [
      'sunday'    => '00:00-23:59',
      'monday'    => '00:00-23:59',
      'tuesday'   => '00:00-23:59',
      'wednesday' => '01:00-23:59',
      'thursday'  => '00:00-23:59',
      'friday'    => '00:00-23:59',
      'saturday'  => '00:00-23:59',
    ];

$availabilityJson = $esc(json_encode($availability, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

// Mensaje por defecto (lo toma el plugin Vanilla vía data-default-msg)
$defaultMsg = (string)($message ?? 'Hola necesito información acerca de  {{url}}');

// Posición del botón flotante
$posClass = (($position ?? 'right') === 'left') ? 'wcs_fixed_left' : 'wcs_fixed_right';
?>
<div class="whatsapp_chat_support <?= $posClass; ?>" id="WhatsappWidget" data-widget="button">
  <button
    type="button"
    class="wcs_button wcs_button_person"
    data-number="<?= $esc($phoneDigits); ?>"
    data-default-msg="<?= $esc($defaultMsg); ?>"
    data-availability='<?= $availabilityJson; ?>'
    aria-label="Abrir chat con <?= $esc($agent_name ?? 'Agente'); ?>"
  >
    <span class="wcs_button_person_img">
      <img src="<?= $esc($img); ?>" alt="<?= $esc($agent_name ?? 'Agente'); ?>">
    </span>
    <span class="wcs_button_person_content">
      <span class="wcs_button_person_name"><?= $esc($agent_name ?? 'Soporte'); ?></span>
      <span class="wcs_button_person_description"><?= $esc($agent_description ?? 'Soporte al cliente'); ?></span>
      <span class="wcs_button_person_status"><?= $esc($agent_status ?? 'Estoy en línea'); ?></span>
    </span>
  </button>
</div>
