<?php

namespace Drupal\odsg_migrate\Commands;

use Consolidation\SiteAlias\SiteAliasManagerAwareInterface;
use Consolidation\SiteAlias\SiteAliasManagerAwareTrait;
use Drush\Commands\DrushCommands;

/**
 * ODSG Drush commandfile.
 */
class OdsgMigrateCommands extends DrushCommands implements SiteAliasManagerAwareInterface {

  use SiteAliasManagerAwareTrait;

  /**
   * Reload ODSG migration configurations.
   *
   * @command odsg:migrate-reload
   * @aliases odsg-mr,odsg-migrate-reload
   * @usage odsg-migrate-reload
   *   Reload all ODSG migration configurations.
   * @validate-module-enabled odsg_migrate
   */
  public function migrateReload() {
    // Uninstall and reinstall all configuration.
    \Drupal::service('config.manager')->uninstall('module', 'odsg_migrate');
    \Drupal::service('config.installer')->installDefaultConfig('module', 'odsg_migrate');

    // Rebuild cache.
    $process = $this->processManager()->drush($this->siteAliasManager()->getSelf(), 'cache-rebuild');
    $process->mustrun();

    $this->logger()->success(dt('Config reload complete.'));
  }

}
