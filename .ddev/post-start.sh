#!/bin/bash
set -e -o pipefail

./.ddev/commands/web/poser

# If db empty, do initial setup
if [ -z "$(drush status --field=bootstrap)" ]; then
  drush si --site-name=custom-admin-toolbar --account-pass=1 -y
  # This normally is take care by config.contrib.yaml but if this was run with
  # freshly cloned project, poser needs to be run manually, but we are automating
  # that
  ./.ddev/commands/web/symlink-project
  drush en -y devel custom_admin_toolbar admin_toolbar_tools
  # Core's Navigation module supersedes Toolbar: it suppresses the toolbar
  # outright for anyone holding "access navigation", leaving this module with
  # nothing to override. It is installed by the standard profile on recent
  # core, so drop it. Checked rather than version-gated, since it is absent
  # on older core and uninstalling would then fail the -e shell.
  if drush pm:list --status=enabled --format=list | grep -qx navigation; then
    drush pm:uninstall -y navigation
  fi
  drush ucrt editor --mail='editor@example.com' --password='1'
  drush urol 'content_editor' 'editor'
  drush cim -y --partial --source=/var/www/html/.ddev/custom_admin_toolbar/config/
  drush scr ./.ddev/custom_admin_toolbar/create-menu-items.php
  cat .ddev/custom_admin_toolbar/custom_admin_toolbar.settings.yml | drush cset --input-format=yaml custom_admin_toolbar.settings \? -
  drush rap authenticated 'access devel information'
  drush rap content_editor 'access toolbar'
fi

# Always output this
drush uli
