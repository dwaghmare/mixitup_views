<?php
/**
 * @file
 * Contains the Mixitup style plugin.
 */

class views_plugin_style_mixitup_views extends views_plugin_style {

  /**
   * Set default options.
   */
  function option_definition() {
    $options = parent::option_definition();
    // Get default options.
    $default_options = mixitup_views_default_options(TRUE);
    foreach ($default_options as $option => $default_value) {
      $options[$option] = array(
        'default' => $default_value,
      );
      if (is_int($default_value)) {
        $options[$option]['bool'] = TRUE;
      }
    }

    return $options;
  }

  /**
   * Render the given style.
   */
  function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);

    // Add Mixitup options to views form.
    $form['mixitup'] = array(
      '#type' => 'fieldset',
      '#title' => t('MixItUp Animation settings'),
    );
    if (mixitup_views_installed()) {
      $options = $this->options;
      $form['animation_enable'] = array(
        '#type' => 'checkbox',
        '#title' => t('Enable Animation'),
        '#default_value' => $options['animation_enable'],
        '#attributes' => array(
          'class' => array('animation_enable'),
        ),
      );
      $form['animation_effects'] = array(
        '#type' => 'textfield',
        '#title' => t('Effects'),
        '#description' => t('The effects for all filter operations as a space-separated string.'),
        '#default_value' => $options['animation_effects'],
      );
      $form['animation_duration'] = array(
        '#type' => 'textfield',
        '#title' => t('Duration'),
        '#description' => t('The duration of the animation in milliseconds.'),
        '#default_value' => $options['animation_duration'],
      );
      $form['animation_easing'] = array(
        '#type' => 'textfield',
        '#title' => t('Easing'),
        '#description' => t('For a full list of accepted values, check out easings.net.'),
        '#default_value' => $options['animation_easing'],
      );
      $form['animation_perspectiveDistance'] = array(
        '#type' => 'textfield',
        '#title' => t('perspectiveDistance'),
        '#description' => t('The perspective value in CSS units applied to the container during animations.'),
        '#default_value' => $options['animation_perspectiveDistance'],
      );
      $form['animation_perspectiveOrigin'] = array(
        '#type' => 'textfield',
        '#title' => t('perspectiveOrigin'),
        '#description' => t('The perspective-origin value applied to the container during animations.'),
        '#default_value' => $options['animation_perspectiveOrigin'],
      );
      $form['animation_queue'] = array(
        '#type' => 'checkbox',
        '#title' => t('Queue'),
        '#description' => t('Enable queuing for all operations received while an another operation is in progress.'),
        '#default_value' => $options['animation_queue'],
        '#attributes' => array('class' => array('animation_queue')),
      );
      $form['animation_queueLimit'] = array(
        '#type' => 'textfield',
        '#title' => t('queueLimit'),
        '#description' => t('The maximum number of operations allowed in the queue at any time.'),
        '#default_value' => $options['animation_queueLimit'],
      );

      foreach (mixitup_views_default_options(TRUE) as $option => $default_value) {
        $form[$option]['#fieldset'] = 'mixitup';
        if ($option != 'animation_enable') {
          $selectors['.animation_enable'] = array('checked' => TRUE);
          if ($option == 'animation_queueLimit') {
            $selectors['.animation_queue'] = array('checked' => TRUE);
          }
          $form[$option]['#states'] = array(
            'visible' => $selectors,
          );
        }
      }
    }
    else {
      // Disable Mixitup.
      $form['mixitup_disabled'] = array(
        '#markup' => t('Please, install MixItUp plugin for using it.'),
        '#fieldset' => 'mixitup',
      );
    }
  }
}
