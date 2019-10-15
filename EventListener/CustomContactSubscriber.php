<?php

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticCustomContactBundle\EventListener;

use Mautic\CoreBundle\CoreEvents;
use Mautic\CoreBundle\Event\CustomButtonEvent;
use Mautic\CoreBundle\Event\CustomContentEvent;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\CoreBundle\Templating\Helper\ButtonHelper;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use Mautic\CoreBundle\Event\CustomTemplateEvent;
use MauticPlugin\MauticCustomContactBundle\Helper\SectionHelper;
class CustomContactSubscriber extends CommonSubscriber
{
    /**
     * @var IntegrationHelper
     */
    protected $integrationHelper;

    private $event;
    private $sectionHelper;
    private $objectId;

    /**
     * ButtonSubscriber constructor.
     *
     * @param IntegrationHelper $helper
     */
    public function __construct(IntegrationHelper $integrationHelper, SectionHelper $sectionHelper)
    {
        $this->integrationHelper = $integrationHelper;
        $this->sectionHelper = $sectionHelper;
    }

    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::VIEW_INJECT_CUSTOM_CONTENT => ['injectContent', 0],
            CoreEvents::VIEW_INJECT_CUSTOM_TEMPLATE => ['customTemplate', 0],
        ];
    }

    /**
     * @param CustomButtonEvent $event
     */
    public function injectContent(CustomContentEvent $event)
    {
        $integration = $this->integrationHelper->getIntegrationObject('CustomContact');

        if (false === $integration || !$integration->getIntegrationSettings()->getIsPublished()) {
            return;
        }
       // dump($event->getVars()["lead"]->getId());die;
    //    dump($event->getViewName());
      //dump($event->getContext());
        // dump($event->getContent());
        // dump($event->getTemplates());
    
        if ($event->getContext() == 'section.left') {
            
            $event->addContent($this->sectionHelper->getTeleInfo($event->getVars()["lead"]->getId()));
        }
        if ($event->getContext() == 'section.right') {
           
            $event->addContent($this->sectionHelper->getDealProfile($event->getVars()["lead"]->getId()));
        }
        if ($event->getContext() == 'section.center') {
            // $data=$event->getVars();
            // dump($data);die;
            $event->addContent($this->sectionHelper->getContactProfile($event->getVars()["lead"]->getId(),$event->getVars()));
        }

        if ($event->getContext() == 'section.tabs') {
            $event->addContent($this->sectionHelper->getTabs($event->getVars()["lead"]->getId(),$event->getVars()));
        }
    }
    public function customTemplate(CustomTemplateEvent $event)
    {
        $integration = $this->integrationHelper->getIntegrationObject('CustomContact');

        if (false === $integration || !$integration->getIntegrationSettings()->getIsPublished()) {
            return;
        }
        if($event->getTemplate() =='MauticLeadBundle:Lead:lead.html.php'){
           $event->setTemplate('MauticCustomContactBundle:Lead:lead.html.php');
        }
    }
}