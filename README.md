# Custom Admin Toolbar

Module aims to override the admin_toolbar module so that site administrators can provide
a custom menu admin toolbar on a per-role basis using menus other than the system admin
menu tree.

## REQUIREMENTS

Drupal 10.2+ or 11, plus the
[Admin Toolbar](https://www.drupal.org/project/admin_toolbar) module.

> [!WARNING]
> This module has no effect while core's **Navigation** module is enabled.
> Navigation was added in Drupal 10.3 and is installed by the standard profile
> on Drupal 11, so a current site is likely affected out of the box. Drupal 10.2
> predates Navigation and is unaffected.

Navigation replaces the classic Toolbar rather than complementing it: core's
`toolbar_page_top()` returns early whenever Navigation is installed and the
current user has the `access navigation` permission, so the toolbar is never
built and the `hook_toolbar_alter()` this module relies on never fires. Per
user, exactly one of the two renders.

Either uninstall Navigation, or revoke `access navigation` from the roles that
should get the overridden toolbar. Note that the standard profile grants that
permission to the Content editor role.

Core plans to move Toolbar to a contributed project before Drupal 12
([#3484850](https://www.drupal.org/project/drupal/issues/3484850)). Admin
Toolbar is splitting branches in response, with 3.x covering core up to 11.3
and 4.x targeting 11.4 and later
([#3565206](https://www.drupal.org/project/admin_toolbar/issues/3565206)),
so the branch this module builds on will matter.

## INSTALLATION

Install as usual at /admin/extend.

## USAGE

1. Navigate to /admin/config/user-interface/custom-admin-toolbar to view the settings form.
(Make sure the "administer site configuration" permission is granted to the user
accessing the module settings form.)

2. Select the specific roles that should view the overridden admin toolbar. To automatically
apply to all roles (except "super administrative" roles) do not select any roles (leave blank).
(Those roles also need the core "access toolbar" permission, or no toolbar renders at all.)

3. Choose the menu source to use as the overridden admin toolbar.

4. Hide extra miscellaneous menu tabs if desired.

5. Save the settings form. Changes to the settings or to the source menu take
effect on the next page load; no cache rebuild is needed.
