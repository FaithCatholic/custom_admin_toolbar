<?php

namespace Drupal\custom_admin_toolbar\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\system\Entity\Menu;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SettingsForm.
 */
class Settings extends ConfigFormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
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
      '#options' => ['' => $this->t('Select a menu')] + $this->get_menus(),
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
      '#title' => $this->t('Hide account tab'),
      '#default_value' => $config->get('hide_user') ? $config->get('hide_user') : 0,
    ];

    $form['tabs']['hide_home'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide home tab'),
      '#default_value' => $config->get('hide_home') ? $config->get('hide_home') : 0,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   *
   */
  public function get_menus($all = TRUE) {
    if ($menus = Menu::loadMultiple()) {
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
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
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
