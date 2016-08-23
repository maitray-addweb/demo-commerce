<?php

namespace Drupal\commerce_product\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Defines the product variation type entity class.
 *
 * @ConfigEntityType(
 *   id = "commerce_product_variation_type",
 *   label = @Translation("Product variation type"),
 *   label_singular = @Translation("Product variation type"),
 *   label_plural = @Translation("Product variation types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count product variation type",
 *     plural = "@count product variation types",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\commerce_product\ProductVariationTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\commerce_product\Form\ProductVariationTypeForm",
 *       "edit" = "Drupal\commerce_product\Form\ProductVariationTypeForm",
 *       "delete" = "Drupal\commerce_product\Form\ProductVariationTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "commerce_product_variation_type",
 *   admin_permission = "administer product types",
 *   bundle_of = "commerce_product_variation",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "lineItemType",
 *     "generateTitle",
 *   },
 *   links = {
 *     "add-form" = "/admin/commerce/config/product-variation-types/add",
 *     "edit-form" = "/admin/commerce/config/product-variation-types/{commerce_product_variation_type}/edit",
 *     "delete-form" = "/admin/commerce/config/product-variation-types/{commerce_product_variation_type}/delete",
 *     "collection" =  "/admin/commerce/config/product-variation-types"
 *   }
 * )
 */
class ProductVariationType extends ConfigEntityBundleBase implements ProductVariationTypeInterface {

  /**
   * The product variation type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The line item type ID.
   *
   * @var string
   */
  protected $lineItemType;

  /**
   * Whether the product variation title should be automatically generated.
   *
   * @var bool
   */
  protected $generateTitle;

  /**
   * {@inheritdoc}
   */
  public function getLineItemTypeId() {
    return $this->lineItemType;
  }

  /**
   * {@inheritdoc}
   */
  public function setLineItemTypeId($line_item_type_id) {
    $this->lineItemType = $line_item_type_id;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function shouldGenerateTitle() {
    return (bool) $this->generateTitle;
  }

  /**
   * {@inheritdoc}
   */
  public function setGenerateTitle($generate_title) {
    $this->generateTitle = $generate_title;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    if ($update && $this->getLineItemTypeId() != $this->original->getLineItemTypeId()) {
      // The line item type ID has changed, clear the relevant cache.
      \Drupal::service('commerce_product.line_item_type_map')->clearCache();
    }
  }

}
