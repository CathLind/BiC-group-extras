*****************************************************
***  Module for our changes to the Group module   ***
*****************************************************

     *********   ******   ******     ******
     *********  ***  ***  *** ***   *** ***
        ***    ***        ***  *** ***  ***
        ***    ***        ***   *****   ***
        ***    ***        ***           ***
        ***     ***  ***  ***           ***
        ***      ******  ***             ***


*****************************************************
***  User friendly titles in add nodes            ***
*****************************************************

When the user tried to add a group node, the title is to complex and not user friendly. This overrides the rout with a more user friendly title.

The idea comes from; https://www.drupal.org/project/group/issues/2867202

If the patch in https://www.drupal.org/project/group/issues/2949408 is not in use, the code has to be modified and the if-statement in line 29 "if ($plugin_id !== 'group_membership') {}  " must be removed.


*****************************************************
***  Views default argument for curren user group ***
*****************************************************

Use the current users group permission as a contextual filter for groups.

The idea comes from; https://www.drupal.org/project/group/issues/2999661




*****************************************************
***  Views default argument Group Content ID      ***
*****************************************************

A Group Content ID views argument which allows for title replacements.

The idea comes from; https://www.drupal.org/project/group/issues/3152953





*****************************************************
*** Hide all the standard tabs on the node form   ***
*****************************************************

On all the node forms, the standard tabs are removed.




*****************************************************
***  Add an add to group field to node form       ***
*****************************************************

Add a field at the end of the node form, where you can connect the node type to a group.

The idea comes from; https://www.drupal.org/project/group_mapping_from_node

The code don't work properly, adds the group to all forms.

/**
 * Implements hook_form_BASE_FORM_ID_alter() for \Drupal\node\NodeForm.
 *
 * Adds the Group form element to the node form.
 *
 */
function tcm_group_extras_form_node_form_alter(&$form, FormStateInterface $form_state, $form_id) {
  // Loading the Group plugin service.
  $group_plugin_service = \Drupal::service('plugin.manager.group_content_enabler');
  // Installed group plugin ids and it will be compared against the current node type.
  $group_installed_content_plugins = $group_plugin_service->getInstalledIds();
  // Default value for group list.
  $default_group = '';
  // Loading the current node object.
  $node = $form_state->getFormObject()->getEntity();
  // Plugin ID formation for current node type.
  $plugin = 'group_node:' . $node->getType();

  // Checking if current node type is not in Groups then don't show the alter form.
  if (!in_array($plugin, $group_installed_content_plugins)) {
    return;
  }

  // Loading current user.
  $account = \Drupal::currentUser();
  // Checking if current user has access to administer the content.
  $access = $account->hasPermission('administer content');

  // If node is not new then load the value of group.
  if (!$node->isNew()) {
    $query = \Drupal::database()->query("SELECT gid FROM {group_content_field_data} WHERE entity_id = :entity_id", [
      ':entity_id' => $node->id()
    ]);
    // Default group.
    $default_group = $query->fetch(\PDO::FETCH_COLUMN);
  }

  // If access is given then create a configuration form section to node.
  if ($access) {
    // If node is new then collapse the configuration section for groups.
    $collapsed = !($node->isNew());
   
    // Adding a new configuration section to the node editing screen.
    $form['groups'] = [
      '#title' => t('Add to group'),
      '#type' => 'details',
      '#weight' => 10,
      '#open' => $collapsed,
      '#group' => 'advanced',
      '#tree' => TRUE,
    ];

    // Populate list of Existing Groups.
    $form['groups']['group'] = [
      '#title' => t('Select Group to map current node'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'group',
      '#default_value' => !empty($default_group) ? \Drupal::entityTypeManager()->getStorage('group')->load($default_group) : '',
      '#description' => t('Type in the group name to map the current node.'),
    ];

    // Custom valiation method to test group type based use cases.
    $form['#validate'][] = 'group_mapping_from_node_custom_validation';
    // Attach new configruation section to entity.
    $form['#entity_builders'][] = 'group_mapping_from_node_entity_builder';
  }
  
    // Removes all the tabs execpt the created group tab 
  unset($form['meta']);
  unset($form['menu']);
  unset($form['path']);
  unset($form['author']);
  unset($form['options']);
  $form['revision_information']['#access'] = false;
}

/**
 * Implements hook_entity_bundle_field_info_alter().
 */
function tcm_group_extras_custom_validation(&$form, FormStateInterface $form_state)
{
    // Load group based on the Group field submitted in node save.
    $groups = $form_state->getValue('groups');
  
    if (empty($groups['group'])) {
        return;
    }

    // Loading the Group TYPE to test the plugin activation for current node.
    $group = \Drupal::entityTypeManager()->getStorage('group')->load($groups['group']);
    // Getting the Group type of loaded Group.
    $group_type = $group->get('type')->getValue();
    // Loading All different Group types available.
    $group_types = \Drupal::entityTypeManager()->getStorage('group_type')->load($group_type[0]['target_id']);
    // Loading plugin IDs/Content Types enabled in the group type of chosen group form node form.
    $group_installed_content_plugins = $group_types->getInstalledContentPlugins()->getInstanceIds();
    // Default value for group list.
    $default_group = '';
    // Loading the current node object.
    $node = $form_state->getFormObject()->getEntity();
    // Plugin ID formation for current node type.
    $plugin = 'group_node:' . $node->getType();
    // Link URL for admin.
    $link_url = \Drupal\Core\Url::fromUri("internal:/admin/group/types/manage/" . $group_type[0]['target_id'] . "/content",  ['attributes' => ['target' => '_blank']]);
    $link = \Drupal\Core\Link::fromTextAndUrl(t('here'), $link_url)->toString();

    // Checking if current node type is not in Groups then don't show the alter form.
    if (!in_array($plugin, $group_installed_content_plugins)) {
        $form_state->setErrorByName('groups', t('This content type is not valid for the <strong>Group Type</strong> plugin of selected group. Please click @here to enable node type plugin.', [
          '@here' => $link,
        ]));
    }
}

/**
 * Implements hook_ENTITY_TYPE_presave() for node entities.
 */
function tcm_group_extras_node_presave(EntityInterface $node) {
  if (!empty($node->groups['group'])) {
    $plugin = 'group_node:' . $node->getType();
    // Loading the Group to attach node.
    $group = \Drupal::entityTypeManager()->getStorage('group')->load($node->groups['group']);
    
    // Removing the existing content references for group mappings.
    if (!$node->isNew()) {
      tcm_group_extras_remove_old_group_references($node);
    }

    // Mapping of entity to group.
    $group->addContent($node, $plugin);
  }
}

/**
 * This method removes the old content references for single group.
 */
function tcm_group_extras_remove_old_group_references(EntityInterface $node) {
  // Finding the Group Content and remove that.
  $group_content = \Drupal::entityTypeManager()->getStorage('group_content')->loadByProperties([
    'entity_id' => $node->id()
  ]);
  // If existing group content is mapped then remove.
  if (!empty($group_content)) {
    foreach ($group_content as $content) {
      $content->delete();
    }
  }
}

/**
 * Entity form builder to add the group information to the node.
 *
 */
function tcm_group_extras_entity_builder($entity_type, NodeInterface $entity, &$form, FormStateInterface $form_state) {
  $entity->groups = $form_state->getValue('groups');
  
  // Always save a revision for non-administrators.
  if (!empty($entity->groups)) {
    //$entity->group = $entity->group['group'];
    if (!\Drupal::currentUser()->hasPermission('administer nodes')) {
        $entity->setNewRevision();
    }
  }
}




