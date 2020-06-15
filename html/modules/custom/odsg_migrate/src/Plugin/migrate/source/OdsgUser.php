<?php

namespace Drupal\odsg_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Extract users from Drupal 7 database.
 *
 * @MigrateSource(
 *   id = "odsg_user",
 *   source_module = "odsg_migrate"
 * )
 */
class OdsgUser extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    return $this->select('users', 'u')
      ->fields('u', [
        'uid',
        'name',
        'pass',
        'mail',
        'signature',
        'signature_format',
        'created',
        'access',
        'login',
        'status',
        'timezone',
        'language',
        'picture',
        'init',
      ]);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      // Base `users` table fields.
      'uid' => $this->t('User ID'),
      'name' => $this->t('Username'),
      'pass' => $this->t('Password'),
      'mail' => $this->t('Email address'),
      'signature' => $this->t('Signature'),
      'signature_format' => $this->t('Signature format'),
      'created' => $this->t('Registered timestamp'),
      'access' => $this->t('Last access timestamp'),
      'login' => $this->t('Last login timestamp'),
      'status' => $this->t('Status'),
      'timezone' => $this->t('Timezone'),
      'language' => $this->t('Language'),
      'picture' => $this->t('Picture'),
      'init' => $this->t('Init'),
      // List of user roles.
      'roles' => $this->t('Roles'),
      // Custom fields.
      'first_name' => $this->t('First Name'),
      'last_name' => $this->t('Last Name'),
      'title' => $this->t('Title'),
      'member_state' => $this->t('ODSG Member State you represent'),
      'duty_station' => $this->t('Current Duty Station'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $uid = $row->getSourceProperty('uid');

    // Roles.
    $query = $this->select('users_roles', 'ur')
      ->fields('ur', ['rid'])
      ->condition('ur.uid', $uid);
    $row->setSourceProperty('roles', $query->execute()->fetchCol());

    // First name.
    $query = $this->select('field_data_field_firstname', 'f')
      ->fields('f', ['field_firstname_value'])
      ->condition('f.entity_id', $uid)
      ->condition('f.entity_type', 'user');
    $row->setSourceProperty('first_name', $query->execute()->fetchField());

    // Last name.
    $query = $this->select('field_data_field_surname', 'f')
      ->fields('f', ['field_surname_value'])
      ->condition('f.entity_id', $uid)
      ->condition('f.entity_type', 'user');
    $row->setSourceProperty('last_name', $query->execute()->fetchField());

    // Title.
    $query = $this->select('field_data_field_title', 'f')
      ->fields('f', ['field_title_value'])
      ->condition('f.entity_id', $uid)
      ->condition('f.entity_type', 'user');
    $row->setSourceProperty('title', $query->execute()->fetchField());

    // ODSG Member State you represent.
    $query = $this->select('field_data_field_odsg_member_state_you_repr', 'f')
      ->fields('f', ['field_odsg_member_state_you_repr_value'])
      ->condition('f.entity_id', $uid)
      ->condition('f.entity_type', 'user');
    $row->setSourceProperty('member_state', $query->execute()->fetchField());

    // Current duty station.
    $query = $this->select('field_data_field_current_duty_station', 'f')
      ->fields('f', ['field_current_duty_station_value'])
      ->condition('f.entity_id', $uid)
      ->condition('f.entity_type', 'user');
    $row->setSourceProperty('duty_station', $query->execute()->fetchField());

    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'uid' => [
        'type' => 'integer',
        'alias' => 'u',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function bundleMigrationRequired() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function entityTypeId() {
    return 'user';
  }

}
