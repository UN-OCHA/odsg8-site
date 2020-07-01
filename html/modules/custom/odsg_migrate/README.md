ODSG Migration to Drupal 8
==========================

Migrated content
----------------

In migration order:

1. URL aliases
2. Users
3. Files
4. Document file entities (media)
5. Image file entities (media)
6. Menu
7. Page nodes (private pages)
8. Menu links
9. Announcement nodes
10. Taxonomy vocabularies
11. Taxonomy terms
12. Custom link nodes (previously page_landing_ nodes)
13. ODSG document nodes
14. Public page nodes (new content type)

**Ignored**

1. Audio file entities (none)
2. Video file entities (none)
3. Story publications (legacy, none)

**Files**

The file migration doesn't copy the files, this will be done independently by
copying the file directories.

TODO: investigate if we can move the images and documents to the `images` and
`documents` folders and add redirections (ex: nginx or redirect module) for the existing documents.

Public pages
------------

Public pages didn't exist in the drupal 7 site. There was a default "welcome"
page with the HID login information for non logged in users and a private 404
page.

To be more flexible, we introduce public pages (accessible to anonymous users)
to replace those, creating the corresponding nodes during the migration.

To make the migration idempotent, we use node ids that don't exist in the D7
database. There is a gap in the node table that we can leverage: ids 20 to 2989
are not used. See migrate_plus.migration.odsg_node_public_page.yml for the
selected Ids.

Layouts
-------

There is currently no way to export individual layouts created via the Drupal 8
layout builder as configuration.

That's not a big issue but we need them for the production migration, so this
module provides a drush command to export those individual layouts created
**during development** so that they can be imported during the migration.

Development steps:

1. Migrate content
2. Create the layouts in Drupal 8
3. Export them with `drush odsg-migrate:export-layouts`
4. Commit the changes

Now everytime the migration is run somewhere, the exported layouts will be
imported when migrating the corresponding nodes.
