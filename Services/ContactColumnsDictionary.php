<?php

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticCustomContactBundle\Services;

use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\LeadBundle\Model\FieldModel;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use Symfony\Component\Translation\TranslatorInterface;

class ContactColumnsDictionary
{
    /**
     * @var FieldModel
     */
    protected $fieldModel;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var CoreParametersHelper
     */
    private $coreParametersHelper;
    /**
     * @var CoreParametersHelper
     */
    private $integrationHelper;
    /**
     * @var array
     */
    private $fieldList = [];

    /**
     * @param FieldModel           $fieldModel
     * @param TranslatorInterface  $translator
     * @param CoreParametersHelper $coreParametersHelper
     */
    public function __construct(FieldModel $fieldModel, TranslatorInterface $translator, CoreParametersHelper $coreParametersHelper, IntegrationHelper $integrationHelper)
    {
        $this->fieldModel           = $fieldModel;
        $this->translator           = $translator;
        $this->coreParametersHelper = $coreParametersHelper;
        $this->integrationHelper    = $integrationHelper;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        $integration = $this->integrationHelper->getIntegrationObject('CustomColumn');
        if (false === $integration || !$integration->getIntegrationSettings()->getIsPublished()) {
            $contact_columns = ['name', 'email', 'location', 'stage', 'points', 'last_active', 'id'];
        } else {
            $entity                 = $integration->getIntegrationSettings();
            $currentFeatureSettings = $entity->getFeatureSettings();
            $contact_columns        = $currentFeatureSettings['contact_columns'];
        }

        $columns = array_flip($contact_columns);
        $fields  = $this->getFields();
        foreach ($columns as $alias=>&$column) {
            if (isset($fields[$alias])) {
                $column = $fields[$alias];
            }
        }

        return $columns;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        if (empty($this->fieldList)) {
            $this->fieldList['name']        = $this->translator->trans('mautic.core.name');
            $this->fieldList['email']       = $this->translator->trans('mautic.core.type.email');
            $this->fieldList['location']    = $this->translator->trans('mautic.lead.lead.thead.location');
            $this->fieldList['stage']       = $this->translator->trans('mautic.lead.stage.label');
            $this->fieldList['points']      = $this->translator->trans('mautic.lead.points');
            $this->fieldList['last_active'] = $this->translator->trans('mautic.lead.lastactive');
            $this->fieldList['id']          = $this->translator->trans('mautic.core.id');
            $this->fieldList                = $this->fieldList + $this->fieldModel->getFieldList(false);
        }

        return $this->fieldList;
    }
}
