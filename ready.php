<?php namespace ProcessWire;

wire()->addHookAfter('Pages::saved', function(HookEvent $event) {
  $page = $event->arguments(0);
  if (!$page instanceof Page || !$page->id) return;

  if (!$page->hasField('fld_images')) return;
  if (!$page->isChanged('fld_images') && !$page->isNew()) return;

  $images = $page->get('fld_images');
  if (!$images instanceof Pageimages || !$images->count()) return;

  foreach ($images as $image) {
    if (!$image instanceof Pageimage) continue;
    $image->size(600, 600);
    $image->width(1920);
  }
});



$wire->addHookAfter('Pageimage::url', function(HookEvent $event) {
  static $n = 0;
  $image = $event->object;
  if(++$n === 1 && in_array($image->ext, [ 'jpeg', 'jpg', 'png' ])) {
    $event->return = $image->webp()->url();
  }
  $n--;
}); 


$wire->addHook('Page::webpInfo', function(HookEvent $event) {

  $page = $event->object;
  $files = $event->wire('files');
  $getArray = $event->arguments(0) === true;
  $fileTotal = 0;
  $webpTotal = 0;
  $qty = 0;

  if($page->hasFilesPath()) {

    $imageFiles = $files->find($page->filesPath(), [
      'extensions' => [ 'jpeg', 'jpg', 'png' ],
      'recursive' => 0
    ]);

    foreach($imageFiles as $file) {

      $info = pathinfo($file);
      $webp = "$info[dirname]/$info[filename].webp";

      if(!file_exists($webp)) {
        // if file.webp does not exist, try file with extension plus .webp
        $webp = "$info[dirname]/$info[basename].webp"; // i.e. file.jpg.webp
        if(!file_exists($webp)) continue; // no webp file available
      }

      // webp file is available
      $qty++;
      $webpTotal += filesize($webp);
      $fileTotal += filesize($file);
    }
  }

  $pct = $fileTotal ? round((($fileTotal-$webpTotal)/$fileTotal)*100) : 0;

  $a = [
    'qty' => $qty,
    'qtyStr' => sprintf(_n('%d file', '%d files', $qty), $qty),
    'pct' => $pct,
    'fileSize' => $fileTotal,
    'fileStr' => wireBytesStr($fileTotal, true),
    'webpSize' => $webpTotal,
    'webpStr' => wireBytesStr($webpTotal, true),
    'saveSize' => $fileTotal - $webpTotal,
    'saveStr' => wireBytesStr($fileTotal - $webpTotal),
  ];

  $a['str'] =
    "$a[fileStr] → $a[webpStr] webp / " .
    "saved $a[saveStr] ($a[pct]%) for $a[qtyStr]";

  $event->return = $getArray ? $a : $a['str'];
});
