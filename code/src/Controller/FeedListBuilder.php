<?php
/**
 * @file
 * Contains Drupal\instagram_social_feed\Controller\RobotListBuilder.
 */

namespace Drupal\instagram_social_feed\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

use Drupal\Core\Url;
/**
 * Provides a listing of robot entities.
 *
 * List Controllers provide a list of entities in a tabular form. The base
 * class provides most of the rendering logic for us. The key functions
 * we need to override are buildHeader() and buildRow(). These control what
 * columns are displayed in the table, and how each row is displayed
 * respectively.
 *
 * Drupal locates the list controller by looking for the "list" entry under
 * "controllers" in our entity type's annotation. We define the path on which
 * the list may be accessed in our module's *.routing.yml file. The key entry
 * to look for is "_entity_list". In *.routing.yml, "_entity_list" specifies
 * an entity type ID. When a user navigates to the URL for that router item,
 * Drupal loads the annotation for that entity type. It looks for the "list"
 * entry under "controllers" for the class to load.
 *
 * @package Drupal\instagram_social_feed\Controller
 *
 * @ingroup instagram_social_feed
 */
class FeedListBuilder extends ConfigEntityListBuilder {

  /**
   * Builds the header row for the entity listing.
   *
   * @return array
   *   A render array structure of header strings.
   *
   * @see Drupal\Core\Entity\EntityListController::render()
   */
  public function buildHeader() {
    //$header['machine_name'] = $this->t('Unique name');
    $header['label'] = $this->t('Unique name');
    $header['search_term'] = $this->t('Search Term');
    $header['auto_publish'] = $this->t('Auto Publish');
    $header['enabled'] = $this->t('Enabled');
    return $header + parent::buildHeader();
  }

  /**
   * Builds a row for an entity in the entity listing.
   *
   * @param EntityInterface $entity
   *   The entity for which to build the row.
   *
   * @return array
   *   A render array of the table row for displaying the entity.
   *
   * @see Drupal\Core\Entity\EntityListController::render()
   */
  public function buildRow(EntityInterface $entity) {
    //$row['machine_name'] = $entity->id();
    $row['label'] = $this->getLabel($entity);
    $row['search_term'] = $entity->search_term;
    $row['auto_publish'] = $entity->auto_publish;
    $row['enabled'] = $entity->enabled;

    return $row + parent::buildRow($entity);
  }

  /**
   * Adds some descriptive text to our entity list.
   *
   * Typically, there's no need to override render(). You may wish to do so,
   * however, if you want to add markup before or after the table.
   *
   * @return array
   *   Renderable array.
   */

  public function render() {


    // Make our links. First the simple page.
    $simple_url = Url::fromRoute('page_example_simple');
    $page_example_simple_link = \Drupal::l($this->t('simple page'), $simple_url);
    // Now the arguments page.
    $arguments_url = Url::fromRoute('page_example_arguments', array('first' => '23', 'second' => '56'));
    $page_example_arguments_link = \Drupal::l($this->t('arguments page'), $arguments_url);

    //Providing module-defined actions
    //https://www.drupal.org/node/2133247

    $build['description'] = array(
      '#markup' => $this->t("<p>The Config Entity Example module defines a"
        . " Robot entity type. This is a list of the Robot entities currently"
        . " in your Drupal site.</p><p>By default, when you enable this"
        . " module, one entity is created from configuration. This is why we"
        . " call them Config Entities. Marvin, the paranoid android, is created"
        . " in the database when the module is enabled.</p><p>You can view a"
        . " list of Robots here. You can also use the 'Operations' column to"
        . " edit and delete Robots.</p>"
        . '<p>The !simple_link just returns a renderable array for display.</p><p>The !arguments_link takes two arguments and displays them, as in @arguments_url</p>',
        array(
          '!simple_link' => $page_example_simple_link,
          '!arguments_link' => $page_example_arguments_link,
          '@arguments_url' => $arguments_url->toString(),
        )
      ),
    );
    $build[] = parent::render();
    return $build;
  }

}
