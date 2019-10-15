<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticCustomContactBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class ConfigType.
 */
class ConfigType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $formModifier = function (FormInterface $form, $currentColumns) {
            $order        = [];
            $orderColumns = [];
            if (!empty($currentColumns)) {
                $orderColumns = array_values($currentColumns);
                $order        = htmlspecialchars(json_encode($orderColumns), ENT_QUOTES, 'UTF-8');
            }

            $form->add(
                'contact_columns',
                ContactColumnsType::class,
                [
                    'label'       => 'mautic.config.tab.columns',
                    'label_attr'  => ['class' => 'control-label'],
                    'attr'        => [
                        'class'         => 'form-control multiselect',
                        'data-sortable' => 'true',
                        'data-order'    => $order,
                    ],
                    'multiple'    => true,
                    'required'    => true,
                    'expanded'    => false,
                    'constraints' => [
                        new NotBlank(
                            ['message' => 'mautic.core.value.required']
                        ),
                    ],
                    'data'=> $orderColumns,
                ]
            );
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $data = $event->getData();
                $columns = isset($data['contact_columns']) ? $data['contact_columns'] : [];
                $formModifier($event->getForm(), $columns);
            }
        );

        // Build the columns selector
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $data    = $event->getData();
                $columns = isset($data['contact_columns']) ? $data['contact_columns'] : [];
                $formModifier($event->getForm(), $columns);
            }
        );
        $builder->add('buttons', 'form_buttons', [
            'apply_text'     => false,
            'save_text'      => 'mautic.core.form.saveandclose',
            'cancel_onclick' => 'javascript:void(0);',
            //'apply_onclick' => 'Mautic.saveColumnConfig()',
            'save_onclick'   => 'Mautic.saveColumnConfig()',
            'cancel_attr'    => [
                'data-dismiss' => 'modal',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'custom_column_config_type';
    }
}
