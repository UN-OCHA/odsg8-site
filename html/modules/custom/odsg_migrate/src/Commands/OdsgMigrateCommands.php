<?php

namespace Drupal\odsg_migrate\Commands;

use Consolidation\SiteAlias\SiteAliasManagerAwareInterface;
use Consolidation\SiteAlias\SiteAliasManagerAwareTrait;
use Drush\Commands\DrushCommands;
use Drupal\Core\Config\ConfigInstallerInterface;
use Drupal\Core\Config\ConfigManager;

/**
 * ODSG Drush commandfile.
 */
class OdsgMigrateCommands extends DrushCommands implements SiteAliasManagerAwareInterface {

  use SiteAliasManagerAwareTrait;

  /**
   * Config Installer.
   *
   * @var Drupal\Core\Config\ConfigInstallerInterface
   */
  protected $configInstaller;

  /**
   * Config Manager.
   *
   * @var Drupal\Core\Config\ConfigManager
   */
  protected $configManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigInstallerInterface $config_installer, ConfigManager $config_manager) {
    $this->configInstaller = $config_installer;
    $this->configManager = $config_manager;
  }

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
    $this->configManager->uninstall('module', 'odsg_migrate');
    $this->configInstaller->installDefaultConfig('module', 'odsg_migrate');

    // Rebuild cache.
    $process = $this->processManager()->drush($this->siteAliasManager()->getSelf(), 'cache-rebuild');
    $process->mustrun();

    $this->logger()->success(dt('Config reload complete.'));
  }

}
