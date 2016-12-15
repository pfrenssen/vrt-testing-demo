<?php

namespace Drupal\Tests\one_two\Unit;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Password\PasswordInterface;
use Drupal\user\UserAuth;
use Drupal\Tests\UnitTestCase;
use Drupal\user\UserInterface;

/**
 * @coversDefaultClass \Drupal\user\UserAuth
 */
class MockingTest extends UnitTestCase {

  /**
   * The mocked entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $entityManager;

  /**
   * The mocked password checker.
   *
   * @var \Drupal\Core\Password\PasswordInterface|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $passwordChecker;

  /**
   * The mocked entity storage service.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $entityStorage;

  /**
   * A mocked user object.
   *
   * @var \Drupal\user\UserInterface|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->entityManager = $this->prophesize(EntityManagerInterface::class);
    $this->passwordChecker = $this->prophesize(PasswordInterface::class);
    $this->entityStorage = $this->prophesize(EntityStorageInterface::class);
    $this->user = $this->prophesize(UserInterface::class);
  }

  /**
   * Tests authenticating users with given username and password.
   *
   * @covers ::authenticate
   * @dataProvider authenticationProvider
   */
  public function testAuthenticate($user_id, $username, $password, $password_is_correct, $expected) {
    // The entity manager will return the storage for the user entity type if
    // requested.
    $this->entityManager->getStorage('user')
      ->willReturn($this->entityStorage->reveal());

    // The entity storage will return the user object by name.
    $this->entityStorage->loadByProperties(['name' => $username])
      ->willReturn([$this->user->reveal()]);

    // It is expected that the user object will return the user ID and password.
    $password_hash = $this->randomMachineName();
    $this->user->getPassword()
      ->willReturn($password_hash);
    $this->user->id()
      ->willReturn($user_id);

    // The password checker will return TRUE if the given password is correct,
    // otherwise it will return FALSE.
    $this->passwordChecker->check($password, $password_hash)
      ->willReturn($password_is_correct);

    // We're not testing the password rehashing here.
    $this->passwordChecker->needsRehash($password_hash)
      ->willReturn(FALSE);

    // If either the username or password are empty, the code should not even
    // bother to load the user object.
    if (empty($username) || empty($password)) {
      $this->entityStorage->loadByProperties(['name' => $username])
        ->shouldNotBeCalled();
    }

    $user_auth = new UserAuth($this->entityManager->reveal(), $this->passwordChecker->reveal());
    $this->assertEquals($expected, $user_auth->authenticate($username, $password));
  }

  /**
   * Data provider for testAuthentication().
   *
   * @return array
   *   An array of test cases. Each test case is an array with the following
   *   values:
   *   - The user ID of the user for which authentication is being tested.
   *   - The user name as entered.
   *   - The password as entered.
   *   - Whether or not the entered password is correct.
   *   - The expected result of UserAuth::authenticate()
   *
   * @see ::testAuthentication()
   */
  public function authenticationProvider() {
    return [
      // A correct user name and password.
      [
        1,
        'admin',
        'hunter2',
        TRUE,
        1,
      ],
      // Incorrect password, should return FALSE.
      [
        1,
        'admin',
        'pass',
        FALSE,
        FALSE,
      ],
      // Missing username, should return FALSE.
      [
        1,
        '',
        'hunter2',
        TRUE,
        FALSE,
      ],
      // Missing password, should return FALSE.
      [
        2,
        'Donald Trump',
        '',
        FALSE,
        FALSE,
      ],
    ];
  }

}
