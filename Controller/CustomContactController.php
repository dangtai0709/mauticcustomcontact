<?php
/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticCustomContactBundle\Controller;

use Mautic\CoreBundle\Controller\CommonController;

class CustomContactController extends CommonController
{
    public function showAction()
    {
        /** @var \Mautic\CoreBundle\Configurator\Configurator $configurator */
        $configurator           = $this->get('mautic.configurator');
        $isWritabale            = $configurator->isFileWritable();
        $params                 = $configurator->getParameters();
        $integrationObj         = $this->get('mautic.helper.integration');
        $integration            = $integrationObj->getIntegrationObject('CustomContact');
        $entity                 = $integration->getIntegrationSettings();
        $currentFeatureSettings = $entity->getFeatureSettings();

        $route = $this->generateUrl(
            'mautic_plugin_custom_column_save',
            [
                'objectAction' => 'saveConfig',
            ]
        );
        $form = $this->createForm(
            'custom_column_config_type',
             ['contact_columns' => $currentFeatureSettings['contact_columns']],
             [
                'action' => $route,
             ]
        );

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form' => $form->createView(),
                ],
                'contentTemplate' => 'MauticCustomContactBundle:CustomContact:CustomContact.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#mautic_contact_columns_index',
                    'mauticContent' => 'contact_columns',
                    'route'         => $route,
                ],
            ]
        );
    }

    public function saveConfigAction()
    {
        $em              = $this->get('doctrine.orm.entity_manager');
        $integrationObj  = $this->get('mautic.helper.integration');
        $integration     = $integrationObj->getIntegrationObject('CustomContact');
        $data            = $this->request->request->all();
        $contact_columns = [];
        if ($data) {
            $contact_columns = $data['custom_column_config_type']['contact_columns'];
        }
        if (empty($contact_columns)) {
            $contact_columns = ['name', 'email', 'location', 'stage', 'points', 'last_active', 'id'];
        }
        $entity                                    = $integration->getIntegrationSettings();
        $currentFeatureSettings                    = $entity->getFeatureSettings();
        $currentFeatureSettings['contact_columns'] = $contact_columns;
        $entity->setFeatureSettings($currentFeatureSettings);
        $em->persist($entity);
        $em->flush();
        $flashes         = [];
        $contentTemplate = 'MauticLeadBundle:Lead:index';
        $activeLink      = '#mautic_contact_index';
        $mauticContent   = 'lead';
        $page            = $this->get('session')->get('mautic.lead.page', 1);
        $returnUrl       = $this->generateUrl('mautic_contact_index', ['page' => $page]);
        $flashes[]       = [
            'type' => 'notice',
            'msg'  => $this->translator->trans('mautic.plugin.contact.custom.column'),
        ];
        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => [
                'page' => $page,
            ],
            'contentTemplate' => $contentTemplate,
            'passthroughVars' => [
                'activeLink'    => $activeLink,
                'mauticContent' => $mauticContent,
                'closeModal'    => true,
            ],
        ];

        return $this->postActionRedirect(
            array_merge(
                $postActionVars,
                [
                    'flashes' => $flashes,
                ]
            )
        );
    }
}
