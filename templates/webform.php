<?php namespace ProcessWire;

$content='';
$title='';
$builder = wire('modules')->get('WebformBuilder');
$id= $page->id;
if($builder) $content= $builder->renderWebformById($id);


include(LAYOUTS.'_Layout.php');