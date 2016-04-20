<?php

namespace Unish;

/**
 * @group base
 */
class annotatedCommandCase extends CommandUnishTestCase {
  public function testExecute() {
    $sites = $this->setUpDrupal(1, TRUE);
    $uri = key($sites);
    $root = $this->webroot();
    $options = array(
      'root' => $root,
      'uri' => $uri,
      'yes' => NULL,
    );

    // Copy the 'woot' module over to the Drupal site we just set up.
    if (UNISH_DRUPAL_MAJOR_VERSION == 8) {
      $this->setupModulesForDrupal8($root);
    }
    else {
      $this->setupModulesForDrupal6and7($root);
    }

    // Enable out module. This will also clear the commandfile cache.
    $this->drush('pm-enable', array('woot'), $options, NULL, NULL, self::EXIT_SUCCESS);

    // drush woot --help
    $this->drush('woot', array(), $options + ['help' => NULL], NULL, NULL, self::EXIT_SUCCESS);
    $output = $this->getOutput();
    $this->assertContains('Woot mightily.', $output);
    $this->assertContains('Aliases: wt', $output);

    // drush help woot
    $this->drush('help', array('woot'), $options, NULL, NULL, self::EXIT_SUCCESS);
    $output = $this->getOutput();
    $this->assertContains('Woot mightily.', $output);

    // drush woot
    $this->drush('woot', array(), $options, NULL, NULL, self::EXIT_SUCCESS);
    $output = $this->getOutput();
    $this->assertEquals('Woot!', $output);

    // drush my-cat --help
    $this->drush('my-cat', array(), $options + ['help' => NULL], NULL, NULL, self::EXIT_SUCCESS);
    $output = $this->getOutput();
    $this->assertContains('This is the my-cat command', $output);
    $this->assertContains('bet alpha --flip', $output);
    $this->assertContains('The first parameter', $output);
    $this->assertContains('The other parameter', $output);
    $this->assertContains('Whether or not the second parameter', $output);
    $this->assertContains('Aliases: c', $output);

    // drush help my-cat
    $this->drush('help', array('my-cat'), $options, NULL, NULL, self::EXIT_SUCCESS);
    $output = $this->getOutput();
    $this->assertContains('This is the my-cat command', $output);

    // drush my-cat bet alpha --flip
    $this->drush('my-cat', array('bet', 'alpha'), $options + ['flip' => NULL], NULL, NULL, self::EXIT_SUCCESS);
    $output = $this->getOutput();
    $this->assertEquals('alphabet', $output);
  }

  public function setupModulesForDrupal8($root) {
    $woot8Module = __DIR__ . '/resources/modules/d8/woot';
    $modulesDir = "$root/modules";
    \symlink($woot8Module, "$modulesDir/woot");
  }

  public function setupModulesForDrupal6and7($root) {
    $wootModule = __DIR__ . '/resources/modules/d7/woot';
    $woot8Module = __DIR__ . '/resources/modules/d8/woot';
    $modulesDir = "$root/sites/all/modules";
    \symlink($wootModule, "$modulesDir/woot");
    if (!file_exists("$wootModule/Command/WootCommands.php")) {
      \symlink("$woot8Module/src/Command/WootCommands.php", "$wootModule/Command/WootCommands.php");
    }
  }
}
