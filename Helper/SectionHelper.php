<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticCustomContactBundle\Helper;

use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Helper\TemplatingHelper;
use Mautic\CoreBundle\Helper\UserHelper;
use Mautic\CoreBundle\Security\Permissions\CorePermissions;
use Mautic\FormBundle\Entity\Form;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use Mautic\LeadBundle\Templating\Helper\AvatarHelper;

/**
 * Class FormTabHelper.
 */
class SectionHelper
{
    /**
     * @var TemplatingHelper
     */
    private $templatingHelper;
    private $avatarHelper;

    /**
     * @var UserHelper
     */
    private $userHelper;

    /**
     * @var CorePermissions
     */
    private $security;

    /**
     * @var SubmissionModel
     */
    // private $submissionModel;

    /**
     * @var IntegrationHelper
     */
    private $integrationHelper;

    /**
     * @var CoreParametersHelper
     */
    private $coreParametersHelper;

    private $resultCache;

    /**
     * @var LeadModel
     */
    private $leadModel;

    /**
     * FormTabHelper constructor.
     *
     * @param TemplatingHelper     $templatingHelper
     * @param UserHelper           $userHelper
     * @param CorePermissions      $security
     * @param IntegrationHelper    $integrationHelper
     * @param CoreParametersHelper $coreParametersHelper
     * @param LeadModel            $leadModel
     */
    public function __construct(
        TemplatingHelper $templatingHelper,
        UserHelper $userHelper,
        CorePermissions $security,
        IntegrationHelper $integrationHelper,
        CoreParametersHelper $coreParametersHelper,
        LeadModel $leadModel,
        AvatarHelper $avatarHelper
    ) {
        $this->templatingHelper = $templatingHelper;
        $this->userHelper = $userHelper;
        $this->security = $security;
        $this->integrationHelper = $integrationHelper;
        $this->coreParametersHelper = $coreParametersHelper;
        $this->leadModel = $leadModel;
        $this->avatarHelper=$avatarHelper;
    }

    public function getTeleInfo($leadId)
    {

        $integration = $this->integrationHelper->getIntegrationObject('CustomContact');

        if (false === $integration || !$integration->getIntegrationSettings()->getIsPublished()) {
            return [];
        }

        $lead = $this->leadModel->getEntity($leadId);

        return $this->templatingHelper->getTemplating()->render(
            'MauticCustomContactBundle:Lead\Section:left.html.php',
            [
                'lead' => $lead,
            ]
        );
    }
    public function getDealProfile($leadId)
    {

        $integration = $this->integrationHelper->getIntegrationObject('CustomContact');

        if (false === $integration || !$integration->getIntegrationSettings()->getIsPublished()) {
            return [];
        }

        $lead = $this->leadModel->getEntity($leadId);

        return $this->templatingHelper->getTemplating()->render(
            'MauticCustomContactBundle:Lead\Section:right.html.php',
            [
                'lead' => $lead,
            ]
        );
    }
    public function getContactProfile($leadId,$data)
    {

        $integration = $this->integrationHelper->getIntegrationObject('CustomContact');

        if (false === $integration || !$integration->getIntegrationSettings()->getIsPublished()) {
            return [];
        }

        $lead = $this->leadModel->getEntity($leadId);
        $isAnonymous = $lead->isAnonymous();
        $leadName       = ($isAnonymous) ? $view['translator']->trans($lead->getPrimaryIdentifier()) : $lead->getPrimaryIdentifier();
        return $this->templatingHelper->getTemplating()->render(
            'MauticCustomContactBundle:Lead\Section:center.html.php',
            [
                'lead' => $lead,
                'img'=>$this->avatarHelper->getAvatar($lead),
                'leadName' =>  $leadName,
                "fields"=>$data["fields"]
            ]
        );
    }
    public function getTabs($leadId,$data)
    {

        $integration = $this->integrationHelper->getIntegrationObject('CustomContact');

        if (false === $integration || !$integration->getIntegrationSettings()->getIsPublished()) {
            return [];
        }

        $lead = $this->leadModel->getEntity($leadId);
        return $this->templatingHelper->getTemplating()->render(
            'MauticCustomContactBundle:Lead\Section:grouptabs.html.php',
            [
                 'lead' => $lead,
                 'events' =>  $data["events"],
                 'data'=>$data,
            ]
        );
    }
}