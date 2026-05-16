<?php
/** @var \ProcessWire\User $user */
/** @var string $code */
/** @var string $activateUrl */
/** @var string $siteName */
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Activate your account</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body { margin:0; padding:0; background:#f6f9fc; font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; color:#0f172a; }
    .wrap { max-width:560px; margin:24px auto; background:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.06); }
    .header { background:#0d6efd; color:#fff; padding:20px 24px; }
    .header h1 { margin:0; font-size:20px; }
    .content { padding:24px; }
    .btn { display:inline-block; background:#0d6efd; color:#fff !important; text-decoration:none; padding:12px 18px; border-radius:8px; }
    .muted { color:#64748b; font-size:14px; }
    a { color:#0d6efd; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="header">
      <h1><?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?></h1>
    </div>
    <div class="content">
      <p>Hi <?= htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8') ?>,</p>
      <p>Thanks for registering. Please activate your account using the button below:</p>

      <p style="margin:24px 0;">
        <a class="btn" href="<?= htmlspecialchars($activateUrl, ENT_QUOTES, 'UTF-8') ?>">Activate account</a>
      </p>

      <p class="muted">If the button does not work, copy and paste this link into your browser:</p>
      <p><a href="<?= htmlspecialchars($activateUrl, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($activateUrl, ENT_QUOTES, 'UTF-8') ?></a></p>

      <p class="muted">If you did not request this, you can safely ignore this email.</p>
    </div>
  </div>
</body>
</html>
