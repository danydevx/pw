<?php
/**
 * Vista: widget-agent.tpl.php (multi-agente compatible)
 *
 * Variables esperadas desde el módulo:
 * - $label, $header, $cta
 * - $message, $phone
 * - $agent_name, $agent_status, $agent_description, $agent_image
 * - $position ('left'|'right'), $openDefault (int), $assetsUrl
 *
 * Opcional:
 * - $agents = [
 *     [
 *       'number'       => '5213321845739',
 *       'name'         => 'Mia Smith',
 *       'description'  => 'Sales Support',
 *       'status'       => "I'm Online",
 *       'image'        => '/path/img/person_5.jpg',
 *       'default_msg'  => 'Hi! I need help about {{url}}',
 *       'availability' => ['monday'=>'08:00-17:30','tuesday'=>'08:00-17:30'] // opcional
 *     ],
 *     ...
 *   ]
 */

$esc = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

// Posición + apertura por defecto
$posClass  = ($position === 'left') ? 'wcs_fixed_left' : 'wcs_fixed_right';
$openClass = !empty($openDefault) ? ' wcs-show' : '';

// Si no llega $agents, armamos uno con los datos single del módulo
if (!isset($agents) || !is_array($agents) || !count($agents)) {
  $agents = [[
    'number'       => preg_replace('/\D+/', '', (string)($phone ?? '')),
    'name'         => (string)($agent_name ?? ''),
    'description'  => (string)($agent_description ?? ''),
    'status'       => (string)($agent_status ?? ''),
    'image'        => (string)($agent_image ?? ''),
    'default_msg'  => (string)($message ?? ''),
    // 'availability' => [] // si quieres controlar disponibilidad
  ]];
}

// Normalizamos cada agente
$normAgents = array_map(function($a) use($assetsUrl) {
  $num  = preg_replace('/\D+/', '', (string)($a['number'] ?? ''));
  $name = (string)($a['name'] ?? '');
  $desc = (string)($a['description'] ?? '');
  $stat = (string)($a['status'] ?? '');
  $img  = (string)($a['image'] ?? '');
  $dmsg = (string)($a['default_msg'] ?? '');
  $avail= $a['availability'] ?? null;

  if ($img === '') {
    $baseAssets = rtrim((string)$assetsUrl, '/').'/';
    $img = $baseAssets . 'img/person_default.jpg';
  }

  return [
    'number'       => $num,
    'name'         => $name,
    'description'  => $desc,
    'status'       => $stat,
    'image'        => $img,
    'default_msg'  => $dmsg,
    'availability' => $avail,
  ];
}, $agents);

?>
<div class="whatsapp_chat_support <?= $posClass . $openClass; ?>" id="WhatsappWidget" data-widget="agent">

  <!-- Etiqueta flotante -->
  <div class="wcs_button_label">
    <?= $esc($label ?: "Questions? Let's Chat"); ?>
  </div>

  <!-- Botón flotante -->
  <button type="button"
          class="wcs_button wcs_button_circle"
          aria-haspopup="dialog"
          aria-controls="wcs_popup"
          aria-expanded="<?= !empty($openDefault) ? 'true' : 'false'; ?>">
    <span class="fa-brands fa-whatsapp" aria-hidden="true"></span>
    <span class="visually-hidden">Open WhatsApp chat</span>
  </button>

  <!-- Popup -->
  <div class="wcs_popup" id="wcs_popup" role="dialog" aria-modal="false" aria-label="WhatsApp Support">
    <button type="button" class="wcs_popup_close" aria-label="Close">
      <span class="fa-solid fa-xmark" aria-hidden="true"></span>
    </button>

    <header class="wcs_popup_header">
      <strong><?= $esc($header ?: 'Need Help? Chat with us'); ?></strong><br>
      <div class="wcs_popup_header_description">
        <?= $esc($cta ?: 'Click one of our representatives below'); ?>
      </div>
    </header>

    <!-- Lista de representantes -->
    <div class="wcs_popup_person_container">
      <?php foreach ($normAgents as $ag): ?>
        <?php
          $dataAvail = '';
          if (is_array($ag['availability']) && count($ag['availability'])) {
            $dataAvail = " data-availability='" . $esc(json_encode($ag['availability'])) . "'";
          }
        ?>
        <article class="wcs_popup_person"
                 data-number="<?= $esc($ag['number']); ?>"
                 data-default-msg="<?= $esc($ag['default_msg']); ?>"<?= $dataAvail; ?>>
          <figure class="wcs_popup_person_img">
            <img src="<?= $esc($ag['image']); ?>" alt="<?= $esc($ag['name']); ?>">
          </figure>
          <div class="wcs_popup_person_content">
            <div class="wcs_popup_person_name"><?= $esc($ag['name']); ?></div>
            <div class="wcs_popup_person_description"><?= $esc($ag['description']); ?></div>
            <div class="wcs_popup_person_status"><?= $esc($ag['status']); ?></div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</div>
