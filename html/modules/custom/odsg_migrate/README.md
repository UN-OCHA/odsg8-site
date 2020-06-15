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
7. Page nodes
8. Menu links
9. Announcement nodes
10. Taxonomy vocabularies
11. Taxonomy terms
12. ODSG document nodes

**Ignored**

1. Audio file entities (none)
2. Video file entities (none)
3. Page landing nodes (legacy, not used)
4. Story publications (legacy, none)

**Files**

The file migration doesn't copy the files, this will be done independently by
copying the file directories.

TODO: investigate if we can move the images and documents to the `images` and
`documents` folders and add redirections (ex: nginx or redirect module) for the existing documents.
