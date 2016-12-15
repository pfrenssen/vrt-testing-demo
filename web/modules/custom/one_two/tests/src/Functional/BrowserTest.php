<?php

namespace Drupal\Tests\one_two\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Example functional test.
 *
 * @group one_two
 */
class BrowserTest extends BrowserTestBase {

  /**
   * A test user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['block', 'node', 'datetime'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic page']);
    $this->user = $this->drupalCreateUser(['edit own page content', 'create page content']);
    $this->drupalPlaceBlock('local_tasks_block');
  }

  /**
   * Tests if the user registration page can be correctly retrieved in tests.
   */
  public function testDrupalGet() {
    $this->drupalGet('user/register');
    $this->assertSession()->pageTextContains('Create new account');
    $this->assertSession()->fieldExists('Email address');
    $this->assertSession()->fieldExists('Username');
    $this->assertSession()->fieldExists('Time zone');
    $this->assertSession()->buttonExists('Create new account');
    $this->assertSession()->pageTextNotContains('vtm nieuws');
  }

  /**
   * Tests the creation of a node through the user interface.
   */
  public function testNodeCreate() {
    $this->drupalLogin($this->user);

    $title = $this->randomString();
    $body = $this->randomString(32);
    $edit = [
      'Title' => $title,
      'Body' => $body,
    ];
    $this->drupalPostForm('node/add/page', $edit, t('Save'));

    $node = $this->drupalGetNodeByTitle($title);
    $this->assertTrue($node);
    $this->assertEquals($title, $node->getTitle());
    $this->assertEquals($body, $node->body->value);

    $this->clickLink(t('Edit'));
    $this->assertSession()->addressEquals($node->toUrl('edit-form', ['absolute' => TRUE]));

    $link_text = 'Edit<span class="visually-hidden">(active tab)</span>';
    $this->assertSession()->responseContains($link_text);

    $this->assertSession()->fieldValueEquals('Title', $title);
    $this->assertSession()->fieldValueEquals('Body', $body);
  }

}
