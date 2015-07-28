<?php

/**
 * @file
 * Definition of Drupal\mixitup_views\Plugin\views\style\MixItUp.
 */

namespace Drupal\mixitup_views\Plugin\views\style;

use Drupal\views\Plugin\views\style\StylePluginBase;
use Drupal\Core\Annotation\Plugin;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Core\Url;

/**
 * Style plugin for MixItUp.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "mixitup_views",
 *   title = @Translation("MixItUp"),
 *   help = @Translation("Display content using MixItUp."),
 *   theme = "mixitup_views_view_mixitup",
 *   theme_file = "mixitup_views.theme.inc",
 *   display_types = {"normal"}
 * )
 */
class MixItUp extends StylePluginBase {

  /**
   * Overrides Drupal\views\Plugin\Plugin::$usesOptions.
   */
  protected $usesOptions = TRUE;

  /**
   * Does the style plugin allows to use style plugins.
   *
   * @var bool
   */
  protected $usesRowPlugin = TRUE;

  /**
   * Does the style plugin support custom css class for the rows.
   *
   * @var bool
   */
  protected $usesRowClass = TRUE;

  /**
   * Mixitup service.
   */
  protected $mixitupFuncService;
  /**
   * Default options.
   */
  protected $defaultOptions;

  /**
   * Does the style plugin support grouping of rows.
   *
   * @var bool
   */
  protected $usesGrouping = FALSE;

  /**
   * Constructor.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, $mixitupFuncService) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->mixitupFuncService = $mixitupFuncService;
    $this->defaultOptions = $this->mixitupFuncService->getDefaultOptions(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('mixitup_views.func_service'));
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    // Get the default options.
    $default_options = $this->defaultOptions;
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
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    // Add Mixitup options to views form.
    $form['mixitup'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('MixItUp Animation settings'),
    );
    if ($this->mixitupFuncService->isMixitupInstalled()) {
      $options = $this->options;
      $form['animation_enable'] = array(
        '#type' => 'checkbox',
        '#title' => $this->t('Enable Animation'),
        '#default_value' => $options['animation_enable'],
        '#attributes' => array(
          'class' => array('animation_enable'),
        ),
      );
      $form['animation_effects'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Effects'),
        '#description' => $this->t('The effects for all filter operations as a space-separated string.'),
        '#default_value' => $options['animation_effects'],
      );
      $form['animation_duration'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Duration'),
        '#description' => $this->t('The duration of the animation in milliseconds.'),
        '#default_value' => $options['animation_duration'],
      );
      $form['animation_easing'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Easing'),
        '#description' => $this->t('For a full list of accepted values, check out easings.net.'),
        '#default_value' => $options['animation_easing'],
      );
      $form['animation_perspectiveDistance'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('perspectiveDistance'),
        '#description' => $this->t('The perspective value in CSS units applied to the container during animations.'),
        '#default_value' => $options['animation_perspectiveDistance'],
      );
      $form['animation_perspectiveOrigin'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('perspectiveOrigin'),
        '#description' => $this->t('The perspective-origin value applied to the container during animations.'),
        '#default_value' => $options['animation_perspectiveOrigin'],
      );
      $form['animation_queue'] = array(
        '#type' => 'checkbox',
        '#title' => $this->t('Queue'),
        '#description' => $this->t('Enable queuing for all operations received while an another operation is in progress.'),
        '#default_value' => $options['animation_queue'],
        '#attributes' => array('class' => array('animation_queue')),
      );
      $form['animation_queueLimit'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('queueLimit'),
        '#description' => $this->t('The maximum number of operations allowed in the queue at any time.'),
        '#default_value' => $options['animation_queueLimit'],
      );

      foreach ($this->defaultOptions as $option => $default_value) {
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
      $sorts = $this->view->displayHandlers->get($this->view->current_display)->getOption('sorts');
      $form['mixitup_sorting_settings'] = array(
        '#type' => 'fieldset',
        '#title' => $this->t('MixItUp Sorting settings'),
      );
      $form['use_sort'] = array(
        '#type' => 'checkbox',
        '#fieldset' => 'mixitup_sorting_settings',
        '#title' => $this->t('Use sorting.'),
        '#description' => $this->t('If you want to add new Sort criteria, add them under views "Sort criteria", at first.'),
        '#default_value' => $options['use_sort'],
        '#attributes' => array(
          'class' => array('use_sort'),
        ),
      );
      if ($sorts) {
        $form['sorts'] = array(
          '#type' => 'div',
          '#fieldset' => 'mixitup_sorting_settings',
        );
        foreach ($sorts as $id => $sort) {
          $sort_id = $sort['table'] . '_' . $sort['field'];
          $form['sorts'][$sort_id] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Label for "!f"', array('!f' => $id)),
            '#description' => $this->t("If you don't want to use it, just make this field empty."),
            '#default_value' => isset($options['sorts'][$sort_id]) ? $options['sorts'][$sort_id] : '',
            '#states' => array(
              'visible' => array(
                '.use_sort' => array('checked' => TRUE),
              ),
            ),
          );
        }
      }

      $form['mixitup_vocab'] = array(
        '#type' => 'fieldset',
        '#title' => $this->t('MixItUp Vocabulary settings'),
      );
      $form['restrict_vocab'] = array(
        '#type' => 'checkbox',
        '#fieldset' => 'mixitup_vocab',
        '#title' => $this->t('Restrict terms to particular vocabulary.'),
        '#default_value' => $options['restrict_vocab'],
        '#attributes' => array(
          'class' => array('restrict_vocab_enable'),
        ),
      );
      // Load all vocabularies.
      $all_vocabs = Vocabulary::loadMultiple();

      $vocabulary_options = array();
      foreach ($all_vocabs as $key_vid => $vocab) {
        $vocabulary_options[$key_vid] = $vocab->get('name');
      }

      $form['restrict_vocab_ids'] = array(
        '#type' => 'select',
        '#fieldset' => 'mixitup_vocab',
        '#title' => $this->t('Select vocabularies'),
        '#multiple' => TRUE,
        '#options' => $vocabulary_options,
        '#default_value' => $options['restrict_vocab_ids'],
        '#states' => array(
          'visible' => array(
            '.restrict_vocab_enable' => array('checked' => TRUE),
          ),
        ),
      );
    }
    else {
      $url = Url::fromUri('https://github.com/patrickkunka/mixitup');
      $mixitup_link = \Drupal::l($this->t('MixItUp'), $url);
      $url_readme = Url::fromUri('base:admin/help/mixitup_views', array(
        'absolute' => TRUE,
        'attributes' => array('target' => '_blank'),
      ));
      $readme_link = \Drupal::l($this->t('README'), $url_readme);
      // Disable Mixitup.
      $form['mixitup_disabled'] = array(
        '#markup' => $this->t('Please, download !mixitup plugin to mixitup_views/js
         directory. For more information read !read. After that, you can use it.', array(
          '!mixitup' => $mixitup_link,
          '!read' => $readme_link,
        )),
        '#fieldset' => 'mixitup',
      );
    }
  }

}
