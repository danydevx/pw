<?php namespace ProcessWire;

echo '<h1>' . wire('sanitizer')->entities($page->title) . '</h1>';
echo '<ul>';
foreach($page->children('template=webform, sort=title') as $item) {
    echo '<li><a href="' . $item->url . '">' . wire('sanitizer')->entities($item->title) . '</a></li>';
}
echo '</ul>';
