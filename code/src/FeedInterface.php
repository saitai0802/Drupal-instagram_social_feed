<?php
/**
 * @file
 * Contains \Drupal\content_entity_example\ContactInterface.
 */

namespace Drupal\instagram_social_feed;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a Contact entity.
 *
 * We have this interface so we can join the other interfaces it extends.
 *
 * @ingroup content_entity_example
 */
interface FeedInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
