<?php

namespace Drupal\odsg_general\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Always deny access to '/admin/people/create'.
    if ($route = $collection->get('user.admin_create')) {
      $route->setRequirement('_access', 'FALSE');
    }
  }

}
