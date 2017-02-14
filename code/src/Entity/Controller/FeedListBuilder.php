<?php

/**
 * @file
 * Contains \Drupal\content_entity_example\Entity\Controller\ContentEntityExampleController.
 */

namespace Drupal\instagram_social_feed\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Provides a list controller for content_entity_example entity.
 *
 * @ingroup content_entity_example
 */
class FeedListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   *
   * We override ::render() so that we can add our own content above the table.
   * parent::render() is where EntityListBuilder creates the table using our
   * buildHeader() and buildRow() implementations.
   */
  public function render() {
    $build['description'] = array(
      '#markup' => $this->t('Content Entity Example implements a Contacts model. These contacts are fieldable entities. You can manage the fields on the <a href="@adminlink">Contacts admin page</a>.', array(
        '@adminlink' => \Drupal::urlGenerator()->generateFromRoute('instagram_social_feed.contact_settings'),
      )),
    );
    $build['table'] = parent::render();
    return $build;
  }

  /**
   * {@inheritdoc}
   *
   * Building the header and content lines for the contact list.
   *
   * Calling the parent::buildHeader() adds a column for the possible actions
   * and inserts the 'edit' and 'delete' links as defined for the entity type.
   */
  public function buildHeader() {
    $header['id'] = $this->t('Feed ID');
    $header['hashTag'] = $this->t('Hash Tag');
    $header['auto_publish'] = $this->t('Auto Publish');
    $header['enabled'] = $this->t('Enable');
    //$header['gender'] = $this->t('Gender');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\content_entity_example\Entity\Contact */
    $row['id'] = $entity->id();
    //$row['hashTag'] = $entity->link();
    $row['hashTag'] = $entity->hashTag->value;
    $row['auto_publish'] = $entity->auto_publish->value;
    $row['enabled'] = $entity->enabled->value;
    //$row['gender'] = $entity->gender->value;
    return $row + parent::buildRow($entity);
  }

}
