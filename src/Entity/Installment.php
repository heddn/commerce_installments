<?php

namespace Drupal\commerce_installments\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Installment entity.
 *
 * @ingroup commerce_installments
 *
 * @ContentEntityType(
 *   id = "commerce_installment",
 *   label = @Translation("Installment"),
 *   label_collection = @Translation("Installments"),
 *   label_singular = @Translation("installment"),
 *   label_plural = @Translation("installments"),
 *   label_count = @PluralTranslation(
 *     singular = "@count installment",
 *     plural = "@count installments",
 *   ),
 *   bundle_label = @Translation("Installment type"),
 *   handlers = {
 *     "storage" = "Drupal\commerce_installments\InstallmentStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\commerce_installments\InstallmentListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\commerce_installments\Form\InstallmentForm",
 *       "add" = "Drupal\commerce_installments\Form\InstallmentForm",
 *       "edit" = "Drupal\commerce_installments\Form\InstallmentForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "access" = "Drupal\commerce_installments\InstallmentAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\commerce_installments\InstallmentHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "commerce_installment",
 *   data_table = "commerce_installment_field_data",
 *   revision_table = "commerce_installment_revision",
 *   revision_data_table = "commerce_installment_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer installment entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/installment/commerce_installment/{commerce_installment}",
 *     "add-page" = "/installment/commerce_installment/add",
 *     "add-form" = "/installment/commerce_installment/add/{commerce_installment_type}",
 *     "edit-form" = "/installment/commerce_installment/{commerce_installment}/edit",
 *     "delete-form" = "/installment/commerce_installment/{commerce_installment}/delete",
 *     "version-history" = "/installment/commerce_installment/{commerce_installment}/revisions",
 *     "revision" = "/installment/commerce_installment/{commerce_installment}/revisions/{commerce_installment_revision}/view",
 *     "revision_revert" = "/installment/commerce_installment/{commerce_installment}/revisions/{commerce_installment_revision}/revert",
 *     "translation_revert" = "/installment/commerce_installment/{commerce_installment}/revisions/{commerce_installment_revision}/revert/{langcode}",
 *     "revision_delete" = "/installment/commerce_installment/{commerce_installment}/revisions/{commerce_installment_revision}/delete",
 *     "collection" = "/installment/commerce_installment",
 *   },
 *   bundle_entity_type = "commerce_installment_type",
 *   field_ui_base_route = "entity.commerce_installment_type.edit_form"
 * )
 */
class Installment extends RevisionableContentEntityBase implements InstallmentInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly, make the commerce_installment owner the
    // revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Installment entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Installment entity.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

}
