<?php

namespace Drupal\custom_admin_toolbar\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the custom admin toolbar settings form.
 */
class Settings extends ConfigFormBase {

  use AutowireTrait;

  /**
   * Constructs a Settings form.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typedConfigManager
   *   The typed config manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    TypedConfigManagerInterface $typedConfigManager,
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {
    parent::__construct($config_factory, $typedConfigManager);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'custom_admin_toolbar.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'custom_admin_toolbar_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('custom_admin_toolbar.settings');

    $roles = [];
    foreach ($this->entityTypeManager->getStorage('user_role')->loadMultiple() as $role) {
      if (!$role->isAdmin()) {
        $roles[$role->id()] = $role->label();
      }
    }

    $form['roles'] = [
      '#description' => 'Ctrl+click to select multiple. Leave blank to override all roles by default.',
      '#default_value' => $config->get('roles') ? $config->get('roles') : NULL,
      '#empty_value' => '_none',
      '#multiple' => TRUE,
      '#options' => $roles,
      '#required' => FALSE,
      '#title' => $this->t('Apply to specific roles'),
      '#type' => 'select',
    ];

    $form['menu_source'] = [
      '#default_value' => $config->get('menu_source') ? $config->get('menu_source') : 'admin',
      '#options' => ['' => $this->t('Select a menu')] + $this->getMenus(),
      '#title' => $this->t('Menu source'),
      '#type' => 'select',
      '#required' => TRUE,
    ];

    $form['tabs'] = [
      '#title' => $this->t('Menu tabs'),
      '#type' => 'fieldset',
    ];

    $form['tabs']['hide_user'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide the account tab'),
      '#description' => $this->t('Removes the tab showing the current user name, which opens a tray with links to their account page and log out.'),
      '#default_value' => $config->get('hide_user') ? $config->get('hide_user') : 0,
    ];

    $form['tabs']['hide_home'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide the "Back to site" tab'),
      '#description' => $this->t('Removes the tab linking from the administration pages back to the front page. It is only visible while viewing an administration page, so hiding it has no effect elsewhere.'),
      '#default_value' => $config->get('hide_home') ? $config->get('hide_home') : 0,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Returns a list of menu labels keyed by menu name.
   *
   * @param bool $all
   *   Whether to include system-provided menus.
   *
   * @return string[]
   *   Menu labels keyed by menu name.
   */
  public function getMenus($all = TRUE) {
    $menus = $this->entityTypeManager->getStorage('menu')->loadMultiple();
    if ($menus) {
      if (!$all) {
        $system_menus = [
          'tools' => 'Tools',
          'admin' => 'Administration',
          'account' => 'User account menu',
          'main' => 'Main navigation',
          'footer' => 'Footer menu',
        ];
        $menus = array_diff_key($menus, $system_menus);
      }
      foreach ($menus as $menu_name => $menu) {
        $menus[$menu_name] = $menu->label();
      }
      asort($menus);
    }
    return $menus;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $values = $form_state->getValues();
    $this->config('custom_admin_toolbar.settings')->set('roles', $values['roles']);
    $this->config('custom_admin_toolbar.settings')->set('menu_source', $values['menu_source']);
    $this->config('custom_admin_toolbar.settings')->set('hide_home', $values['hide_home']);
    $this->config('custom_admin_toolbar.settings')->set('hide_user', $values['hide_user']);
    $this->config('custom_admin_toolbar.settings')->save();
  }

}
