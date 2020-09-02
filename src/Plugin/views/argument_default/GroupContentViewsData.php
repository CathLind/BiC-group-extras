<?php

namespace Drupal\tcm_group_extras\Plugin\views\argument_default;

use Drupal\group\Entity\Storage\GroupContentStorageInterface;
use Drupal\views\Plugin\views\argument\NumericArgument;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Argument handler to accept a Group Content ID.
 *
 * @ViewsArgument("group_content_id")
 */
class GroupContentId extends NumericArgument {

  /**
   * The Group Content Storage.
   *
   * @var \Drupal\group\Entity\Storage\GroupContentStorageInterface
   */
  protected $groupContentStorage;

  /**
   * Constructs the GroupContentId object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\group\Entity\Storage\GroupContentStorageInterface $groupContentStorage
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, GroupContentStorageInterface $groupContentStorage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->groupContentStorage = $groupContentStorage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('group_content')
    );
  }

  /**
   * Get the title of the Group Content.
   */
  public function titleQuery() {
    $titles = [];

    $groupContent = $this->groupContentStorage->loadMultiple($this->value);
    foreach ($groupContent as $content) {
      $titles[] = $content->label();
    }
    return $titles;
  }

}