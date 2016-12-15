<?php

namespace Drupal\Tests\one_two\Kernel;

use Drupal\Component\Utility\Unicode;
use Drupal\KernelTests\KernelTestBase;
use Drupal\block_content\Entity\BlockContent;
use Drupal\block_content\Entity\BlockContentType;

/**
 * An example kernel test.
 *
 * @group one_two
 */
class KernelTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['system', 'block', 'block_content', 'user'];

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->installEntitySchema('block_content');
    $this->installEntitySchema('user');

    $this->entityTypeManager = $this->container->get('entity_type.manager');
  }

  /**
   * Tests creation of custom blocks.
   */
  public function testBlockCreation() {
    // Create a block entity type.
    $bundle = Unicode::strtolower($this->randomMachineName());
    BlockContentType::create([
      'id' => $bundle,
      'label' => $this->randomString(),
    ])->save();

    // Create a block.
    $info = $this->randomMachineName();
    $block = BlockContent::create([
      'info' => $info,
      'type' => $bundle,
    ]);
    $block->save();

    // Load the block from storage and check if the values were saved correctly.
    $this->entityTypeManager->getStorage('block_content')->loadUnchanged($block->id());
    $this->assertEquals($info, $block->label());
    $this->assertEquals($bundle, $block->bundle());
  }

}
