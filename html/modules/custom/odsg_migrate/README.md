ODSG Migration to Drupal 8
==========================

Migrated content
----------------

1. Users
2. Taxonomy vocabularies
3. Taxonomy terms
4. Files
5. Document file entities (media)
6. Image file entities (media)
7. ODSG document nodes
8. Page nodes
9. Announcement nodes

**Ignored**

1. Audio file entities (none)
2. Video file entities (none)
3. Page landing nodes (legacy, not used)
4. Story publications (legacy, none)

**Files**

The migration files don't copy the files, this will be done independently by
copying the file directories.

TODO: investigate if we can move the images and documents to the `images` and
`documents` folders and add redirections (ex: nginx) for the existing documents.
