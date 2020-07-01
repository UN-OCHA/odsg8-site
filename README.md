OCHA Donor Support Group (ODSG) site - Drupal 8 version
=======================================================

Migration
---------

The migration is handled by the [osg_migrate](html/modules/custom/odsg_migrate)
module.

Connection settings to the Drupal 7 database should be defined in a settings.php
file with the name `odsg7`.

Run `drush mim --group=odsg`.

Access & Permissions
--------------------

By default everything is private on the site and documents and pages are only
accessible to the `ODSG Member / UN Employee` role.

A `Public page` content type is provided for pages that can be accessed by
anybody. It's currently only used for the welcome page with the HID login
information and the custom 404 page which contains a link to the homepage.

The [odsg_access](html/modules/custom/odsg_access) module provides view
permissions for each content type.

Theme & Layouts
---------------

The site uses the DSS common design and mainly views for the different blocks
arranged on the pages via the Drupal's built-in Layout Builder module.

Theme customizations are in the
[Common design subtheme](html/themes/custom/common_design_subtheme).

The [odsg_layouts](html/modules/custom/odsg_layouts) module provides additional
layouts to use with the drupal Layout Builder.

External data
-------------

The site has list of documents coming from the OCHA corporate site
(https://www.unocha.org) and funding tables with donor ranking coming from the
OCHA Contributions Tracking System (https://oct.unocha.org).

The blocks and tables are views displays and custom query plugins to get the
data are defined in the [odsg_ocha](html/modules/custom/odsg_ocha) module.

Legacy
------

There are some questionable elements inherited from the Drupal 7 version that
were kept when no exceptionally better way of doing things were found.

**Year field for ODSG document**

The `year` field for ODSG documents is a taxonomy reference field with a static
list of years. The [osg_general](html/modules/custom/odsg_general) module
ensures that a term is created for the current year.

Unfortunately the date module in Drupal 8 doesn't provide a way to select
only years as opposed to the Drupal 7 date module. There is the
[Year Only](https://www.drupal.org/project/yearonly) module that should be
evaluated as a replacement.

**Custom link content type**

This is node type that is used to provide links with a background image to
generate custom menus on the main landing pages (About, Funding, Meetings &
Events and Resources). Those menus are defined via views that combine the links
based on their "Landing Page" category taxonomy reference field.

Some of the links are file urls or external urls. This, combined with the
custom background image makes it difficult to find a replacement like using
nodequeues.

Notes
-----

Some notes related to the initial installation and development are available in
the [notes.md](notes.md) file.

