<?php

namespace Drupal\site_api_key\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\system\Form\SiteInformationForm;

/**
 * Class ExtendedSiteInformationForm.
 */
class ExtendedSiteInformationForm extends SiteInformationForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $site_config = $this->config('system.site');
    $form = parent::buildForm($form, $form_state);
     $form['site_api_information'] = [
      '#type' => 'details',
      '#title' => t('Site Api Information'),
      '#open' => TRUE,
    ];
    $form['site_api_information']['siteapikey'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Site API Key'),
      '#default_value' => $site_config->get('siteapikey') ?: 'No API Key yet',
      '#description' => $this->t("Custom field to set the API Key"),
    ];

    // Change form submit button text to 'Update Configuration'.
    $form['actions']['submit']['#value'] = $this->t('Update configuration');
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('system.site')
      ->set('siteapikey', $form_state->getValue('siteapikey'))
      ->save();
    $this->messenger()->addStatus($this->t('Site API Key has been saved with:%api', [
      '%api' => $form_state->getValue('siteapikey'),
    ]));
    parent::submitForm($form, $form_state);
  }

}
