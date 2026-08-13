<?php

namespace Drupal\custom_admin_toolbar\Render\Element;

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
   * @param array $element
   *   The render element.
   *
   * @return array
   *   The render element with the administration menu added.
   */
  public static function preRenderTray($element) {
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
    if (!empty($element['administration_menu']['#items']) && !empty($build['#items'])) {
      $element['administration_menu']['#items'] += $build['#items'];
    }
    else {
      $element['administration_menu'] = $build;
    }
    return $element;
  }

}
