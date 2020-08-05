<?php

namespace Drupal\Tests\odsg_general\ExistingSite;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Test homepage for anonymous.
 */
class HomePageTest extends ExistingSiteBase {

  /**
   * Test home.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testHomePage() {
    $this->drupalGet('/welcome');
    $this->assertSession()->statusCodeEquals(200);

    $this->assertSession()->pageTextContains('Welcome to OCHA Donor Support Group');
    $this->assertSession()->pageTextContains('Log in with');
    $this->assertSession()->elementAttributeContains('css', '.odsg-hid-login__logo', 'alt', 'Humanitarian ID');
  }

}
