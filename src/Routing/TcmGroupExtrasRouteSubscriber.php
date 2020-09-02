<?php

namespace Drupal\tcm_group_extras\Routing;

use Drupal\group\Entity\GroupInterface;
use Drupal\node\Entity\NodeType;
use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class TcmGroupExtrasRouteSubscriber.
 *
 * Listens to the dynamic route events.
 */
class TcmGroupExtrasRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('entity.group_content.create_form')) {
      $route->setDefault("_title_callback", '\Drupal\tcm_group_extras\Routing\TcmGroupExtrasRouteSubscriber::gcCreateFormTitle');
         }
   }
   
   public function gcCreateFormTitle(GroupInterface $group, $plugin_id) {
    /** @var \Drupal\group\Plugin\GroupContentEnablerInterface $plugin */

 if ($plugin_id !== 'group_membership') {  
    $plugin = $group->getGroupType()->getContentPlugin($plugin_id);
    $content_type = NodeType::load($plugin->getEntityBundle());
    return t('@group: Add @name content', ['@name' => $content_type->label(), '@group' => $group->label()]);
  }
   }

}
