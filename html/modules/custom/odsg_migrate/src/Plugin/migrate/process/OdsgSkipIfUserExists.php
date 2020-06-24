<?php

namespace Drupal\odsg_migrate\Plugin\migrate\process;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Skip the whole row if the user exists already.
 *
 * @MigrateProcessPlugin(
 *   id = "odsg_skip_if_user_exists"
 * )
 *
 * Available configuration keys:
 * - uids: list of user ids that should be skipped if they alreaydy exists,
 *         for example 0 (anonymous) and 1 (admin). The user id passed as source
 *         will be compared to that list and the existence check will only apply
 *         if it is in the list.
 *
 *
 * Example usage with minimal configuration:
 *
 * @code
 *   uid:
 *     plugin: odsg_skip_if_user_exists
 *     source: uid
 * @endcode
 *
 * The above example will skip the whole row if a user with the same user id
 * already exists in the destination database.
 *
 * Example usage with full configuration:
 *
 * @code
 *   uid:
 *     plugin: odsg_skip_if_user_exists
 *     source: uid
 *     uids:
 *       - 0
 *       - 1
 * @endcode
 *
 * The above example will skip the whole row if the uid is 0 or 1 and a user
 * with the same id already exists in the destination database.
 */
class OdsgSkipIfUserExists extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * EntityExists constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The user entity storage.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->storage = $storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Validate that the value is a proper positive integer.
    if (filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) === FALSE) {
      throw new MigrateException('Invalid user id passed a source to Skip If User exists plugin.');
    }
    print_r([$value, $this->configuration['uids']]);

    // Get the list of user ids to compare the value with.
    $uids = [];
    if (isset($this->configuration['uids'])) {
      if (!is_array($this->configuration['uids'])) {
        throw new MigrateException('Invalid uids configuration for the Skip If User Exists plugin.');
      }

      // Nothing to do if a list of user ids was provided but the current uid
      // is not in the list.
      if (!in_array($value, $this->configuration['uids'])) {
        return $value;
      }
    }

    // Check if a user with the given user id exists. Skip if that is the case.
    $result = $this->storage->getQuery()->condition('uid', $value)->execute();
    print_r([$result]);
    if (!empty($result)) {
      throw new MigrateSkipRowException();
    }

    return $value;
  }

}
