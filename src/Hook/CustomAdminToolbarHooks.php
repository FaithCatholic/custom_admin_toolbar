<?php

declare(strict_types=1);

namespace Drupal\custom_admin_toolbar\Hook;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Hook\Order\Order;
use Drupal\Core\Session\AccountInterface;
use Drupal\custom_admin_toolbar\Render\Element\CustomAdminToolbar;

/**
 * Hook implementations for custom_admin_toolbar.
 */
class CustomAdminToolbarHooks {

  /**
   * Constructs a CustomAdminToolbarHooks object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The current user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(
    protected ConfigFactoryInterface $configFactory,
    protected AccountInterface $currentUser,
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Implements hook_toolbar_alter().
   *
   * Ordered last so the tray built by admin_toolbar is already in place.
   *
   * @phpstan-param array<string, mixed> $items
   */
  #[Hook('toolbar_alter', order: Order::Last)]
  public function toolbarAlter(array &$items): void {
    $config = $this->configFactory->get('custom_admin_toolbar.settings');

    // The toolbar varies by these settings and by the roles they are matched
    // against. Record that unconditionally, so the cached toolbar is also
    // invalidated when a change starts or stops the override applying.
    $items['#cache']['tags'] = Cache::mergeTags($items['#cache']['tags'] ?? [], $config->getCacheTags());
    $items['#cache']['contexts'] = Cache::mergeContexts($items['#cache']['contexts'] ?? [], ['user.roles']);

    // Determine if the user is user 1, or has a super administrator role,
    // since they always get the original, non-custom admin toolbar.
    $roles = $this->currentUser->getRoles();
    $is_admin = (int) $this->currentUser->id() === 1;
    if (!$is_admin) {
      // loadMultiple() skips role IDs with no matching entity, so a stale role
      // reference on the account cannot fatal here.
      $storage = $this->entityTypeManager->getStorage('user_role');
      foreach ($storage->loadMultiple($roles) as $role) {
        if ($role->isAdmin()) {
          $is_admin = TRUE;
          break;
        }
      }
    }

    if ($is_admin) {
      return;
    }

    // Override depending on the role settings. No selection enables all
    // non-admin roles.
    $configured = $config->get('roles');
    if ($configured && (!is_array($configured) || !array_intersect($configured, $roles))) {
      return;
    }

    unset($items['administration']['tray']['toolbar_administration']['#lazy_builder']);
    unset($items['administration']['tray']['toolbar_administration']['#create_placeholder']);
    $items['administration']['tray']['toolbar_administration']['#pre_render'][] = [
      CustomAdminToolbar::class,
      'preRenderTray',
    ];
    $items['administration']['#attached']['library'][] = 'admin_toolbar/toolbar.tree';

    if ($config->get('hide_home')) {
      unset($items['home']);
    }

    if ($config->get('hide_user')) {
      unset($items['user']);
    }
  }

}
