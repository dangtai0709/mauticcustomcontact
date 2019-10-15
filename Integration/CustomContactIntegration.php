<?php


/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticCustomContactBundle\Integration;

use Mautic\CoreBundle\Configurator\Configurator;
use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\CoreBundle\Helper\CacheHelper;
use Mautic\PluginBundle\Integration\AbstractIntegration;
use MauticPlugin\MauticCustomContactBundle\Form\Type\ContactColumnsType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class CustomContactIntegration extends AbstractIntegration
{
    protected $configurator;
    protected $cacheHelper;

    public function __construct(MauticFactory $factory, Configurator $configurator = null, CacheHelper $cacheHelper=null)
    {
        $this->configurator = $configurator;
        $this->cacheHelper  = $cacheHelper;
        parent::__construct($factory);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getName()
    {
        return 'CustomContact';
    }

    public function getDisplayName()
    {
        return 'Custom Contact Detail';
    }

    public function getIcon()
    {
        return 'plugins/MauticCustomContactBundle/Assets/img/custome-column-icon.png';
    }

    /**
     * @return array
     */
    public function getFormSettings()
    {
        return [
            'requires_callback'      => false,
            'requires_authorization' => false,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getAuthenticationType()
    {
        return 'none';
    }

    /**
     * @param \Mautic\PluginBundle\Integration\Form|FormBuilder $builder
     * @param array                                             $data
     * @param string                                            $formArea
     */
    public function appendToForm(&$builder, $data, $formArea)
    {
       
    }
}
