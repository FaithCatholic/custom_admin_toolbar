# Custom Admin Toolbar

Module aims to override the admin_toolbar module so that site administrators can provide
a custom menu admin toolbar on a per-role basis using menus other than the system admin
menu tree.

## REQUIREMENTS

> [!WARNING]
> This module has no effect while core's **Navigation** module is enabled.

Navigation replaces the classic Toolbar rather than complementing it. Core's
`toolbar_page_top()` returns early whenever Navigation is installed and the
current user has the `access navigation` permission, so the toolbar is never
built and the `hook_toolbar_alter()` this module relies on never fires. The two
cannot be combined; per user, exactly one of them renders.

To use this module, either uninstall Navigation, or revoke `access navigation`
from the roles that should get the overridden toolbar.

Navigation ships enabled with the standard profile on recent core, and core
plans to move Toolbar to a contributed project before Drupal 12. See
[#3484850](https://www.drupal.org/project/drupal/issues/3484850).

## INSTALLATION

Install as usual at /admin/extend.

## USAGE

1. Navigate to /admin/config/user-interface/custom-admin-toolbar to view the settings form.
(Make sure the "administer site configuration" is permission is granted to the user
accessing the module settings form.)

2. Select the specific roles that should view the overridden admin toolbar. To automatically
apply to all roles (except "super administrative" roles) do not select any roles (leave blank).
(Those roles also need the core "access toolbar" permission, or no toolbar renders at all.)

3. Choose the menu source to use as the overridden admin toolbar.

4. Hide extra miscellaneous menu tabs if desired.

5. Save the settings form, and clear the website cache.
