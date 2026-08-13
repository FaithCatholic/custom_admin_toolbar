<?php

namespace Drupal\custom_admin_toolbar\Render\Element;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\admin_toolbar\Render\Element\AdminToolbar;

/**
 * Renders the admin toolbar tray from a configurable menu source.
 */
class CustomAdminToolbar extends AdminToolbar implements TrustedCallbackInterface {

  /**
   * Builds the administration menu tray from the configured menu source.
   *
   * @param array<string, mixed> $element
   *   The render element.
   *
   * @return array<string, mixed>
   *   The render element with the administration menu added.
   */
  public static function preRenderTray(array $element): array {
    $config = \Drupal::service('config.factory')->get('custom_admin_toolbar.settings');
    $menu_tree = \Drupal::service('toolbar.menu_tree');
    $parameters = new MenuTreeParameters();
    $parameters->setMaxDepth(4)->onlyEnabledLinks();
    $menu_source = $config->get('menu_source') ? $config->get('menu_source') : 'admin';
    $tree = $menu_tree->load($menu_source, $parameters);
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
      ['callable' => 'custom_admin_toolbar_tools_menu_navigation_links'],
    ];
    $tree = $menu_tree->transform($tree, $manipulators);
    $build = $menu_tree->build($tree);

    // Merging only '#items' would drop the tree's cache metadata, leaving the
    // rendered toolbar stale after menu or settings changes.
    $cacheability = CacheableMetadata::createFromRenderArray($build)
      ->addCacheableDependency($config);

    if (!empty($element['administration_menu']['#items']) && !empty($build['#items'])) {
      $element['administration_menu']['#items'] += $build['#items'];
    }
    else {
      $element['administration_menu'] = $build;
    }

    $cacheability
      ->merge(CacheableMetadata::createFromRenderArray($element['administration_menu']))
      ->applyTo($element['administration_menu']);

    return $element;
  }

}
