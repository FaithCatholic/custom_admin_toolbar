<?php

$menu_link_storage = \Drupal::entityTypeManager()->getStorage('menu_link_content');
$menu_link_storage->create([
  'title' => 'Test #1',
  'link' => ['uri' => 'internal:/test1'],
  'menu_name' => 'custom-admin-menu',
])->save();
$menu_link_storage->create([
  'title' => 'Test #2',
  'link' => ['uri' => 'internal:/test2'],
  'menu_name' => 'custom-admin-menu',
])->save();
