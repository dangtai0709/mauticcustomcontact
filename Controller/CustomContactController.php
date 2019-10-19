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
use Mautic\LeadBundle\Controller\LeadController;
use Mautic\LeadBundle\Entity\LeadList;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Form\FormError;

class CustomContactController extends LeadController
{
 
    public function indexAction($page = 1)
    {
        if(!$this->getIsPublished()){
            return parent::indexAction();
        }
        //set some permissions
        $permissions = $this->get('mautic.security')->isGranted(
            [
                'lead:leads:viewown',
                'lead:leads:viewother',
                'lead:leads:create',
                'lead:leads:editown',
                'lead:leads:editother',
                'lead:leads:deleteown',
                'lead:leads:deleteother',
                'lead:imports:view',
                'lead:imports:create',
            ],
            'RETURN_ARRAY'
        );

        if (!$permissions['lead:leads:viewown'] && !$permissions['lead:leads:viewother']) {
            return $this->accessDenied();
        }

        $this->setListFilters();

        /** @var \Mautic\LeadBundle\Model\LeadModel $model */
        $model   = $this->getModel('lead');
        $session = $this->get('session');
        //set limits
        $limit = $session->get('mautic.lead.limit', $this->get('mautic.helper.core_parameters')->getParameter('default_pagelimit'));
        $start = ($page === 1) ? 0 : (($page - 1) * $limit);
        if ($start < 0) {
            $start = 0;
        }

        $search = $this->request->get('search', $session->get('mautic.lead.filter', ''));
        $session->set('mautic.lead.filter', $search);

        //do some default filtering
        $orderBy    = $session->get('mautic.lead.orderby', 'l.last_active');
        $orderByDir = $session->get('mautic.lead.orderbydir', 'DESC');

        $filter      = ['string' => $search, 'force' => ''];
        $translator  = $this->get('translator');
        $anonymous   = $translator->trans('mautic.lead.lead.searchcommand.isanonymous');
        $listCommand = $translator->trans('mautic.lead.lead.searchcommand.list');
        $mine        = $translator->trans('mautic.core.searchcommand.ismine');
        $indexMode   = $this->request->get('view', $session->get('mautic.lead.indexmode', 'list'));

        $session->set('mautic.lead.indexmode', $indexMode);

        $anonymousShowing = false;
        if ($indexMode != 'list' || ($indexMode == 'list' && strpos($search, $anonymous) === false)) {
            //remove anonymous leads unless requested to prevent clutter
            $filter['force'] .= " !$anonymous";
        } elseif (strpos($search, $anonymous) !== false && strpos($search, '!'.$anonymous) === false) {
            $anonymousShowing = true;
        }

        if (!$permissions['lead:leads:viewother']) {
            $filter['force'] .= " $mine";
        }

        $results = $model->getEntities([
            'start'          => $start,
            'limit'          => $limit,
            'filter'         => $filter,
            'orderBy'        => $orderBy,
            'orderByDir'     => $orderByDir,
            'withTotalCount' => true,
        ]);

        $count = $results['count'];
        unset($results['count']);

        $leads = $results['results'];
        unset($results);

        if ($count && $count < ($start + 1)) {
            //the number of entities are now less then the current page so redirect to the last page
            if ($count === 1) {
                $lastPage = 1;
            } else {
                $lastPage = (ceil($count / $limit)) ?: 1;
            }
            $session->set('mautic.lead.page', $lastPage);
            $returnUrl = $this->generateUrl('mautic_contact_index', ['page' => $lastPage]);

            return $this->postActionRedirect(
                [
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $lastPage],
                    'contentTemplate' => 'MauticCustomContactBundle:Lead:index',
                    'passthroughVars' => [
                        'activeLink'    => '#mautic_contact_index',
                        'mauticContent' => 'lead',
                    ],
                ]
            );
        }

        //set what page currently on so that we can return here after form submission/cancellation
        $session->set('mautic.lead.page', $page);

        $tmpl = $this->request->isXmlHttpRequest() ? $this->request->get('tmpl', 'index') : 'index';

        $listArgs = [];
        if (!$this->get('mautic.security')->isGranted('lead:lists:viewother')) {
            $listArgs['filter']['force'] = " $mine";
        }

        $lists = $this->getModel('lead.list')->getUserLists();
        //segment
        $list      = new LeadList();
        $listModel = $this->getModel('lead.list');
        $action    = $this->generateUrl('mautic_contact_action', ['objectAction' => 'newSeg']);
        $form      = $listModel->createForm($list, $this->get('form.factory'), $action);
        $segments  = $listModel->getEntities();
        //check to see if in a single list
        $inSingleList = (substr_count($search, "$listCommand:") === 1) ? true : false;
        $list         = [];
        if ($inSingleList) {
            preg_match("/$listCommand:(.*?)(?=\s|$)/", $search, $matches);

            if (!empty($matches[1])) {
                $alias = $matches[1];
                foreach ($lists as $l) {
                    if ($alias === $l['alias']) {
                        $list = $l;
                        break;
                    }
                }
            }
        }

        // Get the max ID of the latest lead added
        $maxLeadId = $model->getRepository()->getMaxLeadId();

        // We need the EmailRepository to check if a lead is flagged as do not contact
        /** @var \Mautic\EmailBundle\Entity\EmailRepository $emailRepo */
        $emailRepo    = $this->getModel('email')->getRepository();
        $listStatus   = [];
        $statusFields =  $this->getModel('lead.field')->getEntityByAlias('status');
        $urlAddStatus = '';
        if ($statusFields) {
            $urlAddStatus = $this->generateUrl('mautic_contact_index').'/fields/edit/'.$statusFields->getId();
            $listStatus   =  $statusFields->getProperties()['list'];
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'searchValue'      => $search,
                    'columns'          => $this->get('mautic.service.contact_columnnd_dictionary')->getColumns(),
                    'items'            => $leads,
                    'page'             => $page,
                    'totalItems'       => $count,
                    'limit'            => $limit,
                    'permissions'      => $permissions,
                    'tmpl'             => $tmpl,
                    'indexMode'        => $indexMode,
                    'lists'            => $lists,
                    'currentList'      => $list,
                    'security'         => $this->get('mautic.security'),
                    'inSingleList'     => $inSingleList,
                    'noContactList'    => $emailRepo->getDoNotEmailList(array_keys($leads)),
                    'maxLeadId'        => $maxLeadId,
                    'anonymousShowing' => $anonymousShowing,
                    'listStatus'       => $listStatus,
                    'urlAddStatus'     => $urlAddStatus,
                    'form'             => $this->setFormTheme($form, 'MauticLeadBundle:List:form.html.php', 'MauticCustomContactBundle:FormTheme\Filter'),
                    'filters'          => $segments,
                ],
                'contentTemplate' => "MauticCustomContactBundle:Lead:{$indexMode}.html.php",
                'passthroughVars' => [
                    'activeLink'    => '#mautic_contact_index',
                    'mauticContent' => 'lead',
                    'route'         => $this->generateUrl('mautic_contact_index', ['page' => $page]),
                ],
            ]
        );
    }

    /**
     * Deletes the entity.
     *
     * @param   $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($objectId)
    {
        if(!$this->getIsPublished()){
            return parent::deleteAction();
        }
        $page      = $this->get('session')->get('mautic.lead.page', 1);
        $returnUrl = $this->generateUrl('mautic_contact_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticCustomContactBundle:Lead:index',
            'passthroughVars' => [
                'activeLink'    => '#mautic_contact_index',
                'mauticContent' => 'lead',
            ],
        ];

        if ($this->request->getMethod() == 'POST') {
            $model  = $this->getModel('lead.lead');
            $entity = $model->getEntity($objectId);

            if ($entity === null) {
                $flashes[] = [
                    'type'    => 'error',
                    'msg'     => 'mautic.lead.lead.error.notfound',
                    'msgVars' => ['%id%' => $objectId],
                ];
            } elseif (!$this->get('mautic.security')->hasEntityAccess(
                'lead:leads:deleteown',
                'lead:leads:deleteother',
                $entity->getPermissionUser()
            )) {
                return $this->accessDenied();
            } elseif ($model->isLocked($entity)) {
                return $this->isLocked($postActionVars, $entity, 'lead.lead');
            } else {
                $model->deleteEntity($entity);

                $identifier = $this->get('translator')->trans($entity->getPrimaryIdentifier());
                $flashes[]  = [
                    'type'    => 'notice',
                    'msg'     => 'mautic.core.notice.deleted',
                    'msgVars' => [
                        '%name%' => $identifier,
                        '%id%'   => $objectId,
                    ],
                ];
            }
        } //else don't do anything

        return $this->postActionRedirect(
            array_merge(
                $postActionVars,
                [
                    'flashes' => $flashes,
                ]
            )
        );
    }

    /**
     * @param int $objectId
     *
     * @return JsonResponse
     */
    public function emailAction($objectId = 0)
    {
        if(!$this->getIsPublished()){
            return parent::emailAction();
        }
        $valid = $cancelled = false;

        /** @var \Mautic\LeadBundle\Model\LeadModel $model */
        $model = $this->getModel('lead');

        /** @var \Mautic\LeadBundle\Entity\Lead $lead */
        $lead = $model->getEntity($objectId);

        if (
            $lead === null
            || !$this->get('mautic.security')->hasEntityAccess(
                'lead:leads:viewown',
                'lead:leads:viewother',
                $lead->getPermissionUser()
            )
        ) {
            return $this->modalAccessDenied();
        }

        $leadFields       = $lead->getProfileFields();
        $leadFields['id'] = $lead->getId();
        $leadEmail        = $leadFields['email'];
        $leadName         = $leadFields['firstname'].' '.$leadFields['lastname'];

        // Set onwer ID to be the current user ID so it will use his signature
        $leadFields['owner_id'] = $this->get('mautic.helper.user')->getUser()->getId();

        // Check if lead has a bounce status
        $dnc    = $this->getDoctrine()->getManager()->getRepository('MauticLeadBundle:DoNotContact')->getEntriesByLeadAndChannel($lead, 'email');
        $inList = ($this->request->getMethod() == 'GET')
            ? $this->request->get('list', 0)
            : $this->request->request->get(
                'lead_quickemail[list]',
                0,
                true
            );
        $email  = ['list' => $inList];
        $action = $this->generateUrl('mautic_contact_action', ['objectAction' => 'email', 'objectId' => $objectId]);
        $form   = $this->get('form.factory')->create('lead_quickemail', $email, ['action' => $action]);

        if ($this->request->getMethod() == 'POST') {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $email = $form->getData();

                    $bodyCheck = trim(strip_tags($email['body']));
                    if (!empty($bodyCheck)) {
                        $mailer = $this->get('mautic.helper.mailer')->getMailer();

                        // To lead
                        $mailer->addTo($leadEmail, $leadName);

                        // From user
                        $user = $this->get('mautic.helper.user')->getUser();

                        $mailer->setFrom(
                            $email['from'],
                            empty($email['fromname']) ? null : $email['fromname']
                        );

                        // Set Content
                        $mailer->setBody($email['body']);
                        $mailer->parsePlainText($email['body']);

                        // Set lead
                        $mailer->setLead($leadFields);
                        $mailer->setIdHash();

                        $mailer->setSubject($email['subject']);

                        // Ensure safe emoji for notification
                        $subject = EmojiHelper::toHtml($email['subject']);
                        if ($mailer->send(true, false, false)) {
                            $mailer->createEmailStat();
                            $this->addFlash(
                                'mautic.lead.email.notice.sent',
                                [
                                    '%subject%' => $subject,
                                    '%email%'   => $leadEmail,
                                ]
                            );
                        } else {
                            $errors = $mailer->getErrors();

                            // Unset the array of failed email addresses
                            if (isset($errors['failures'])) {
                                unset($errors['failures']);
                            }

                            $form->addError(
                                new FormError(
                                    $this->get('translator')->trans(
                                        'mautic.lead.email.error.failed',
                                        [
                                            '%subject%' => $subject,
                                            '%email%'   => $leadEmail,
                                            '%error%'   => (is_array($errors)) ? implode('<br />', $errors) : $errors,
                                        ],
                                        'flashes'
                                    )
                                )
                            );
                            $valid = false;
                        }
                    } else {
                        $form['body']->addError(
                            new FormError(
                                $this->get('translator')->trans('mautic.lead.email.body.required', [], 'validators')
                            )
                        );
                        $valid = false;
                    }
                }
            }
        }

        if (empty($leadEmail) || $valid || $cancelled) {
            if ($inList) {
                $route          = 'mautic_contact_index';
                $viewParameters = [
                    'page' => $this->get('session')->get('mautic.lead.page', 1),
                ];
                $func = 'index';
            } else {
                $route          = 'mautic_contact_action';
                $viewParameters = [
                    'objectAction' => 'view',
                    'objectId'     => $objectId,
                ];
                $func = 'view';
            }

            return $this->postActionRedirect(
                [
                    'returnUrl'       => $this->generateUrl($route, $viewParameters),
                    'viewParameters'  => $viewParameters,
                    'contentTemplate' => 'MauticCustomContactBundle:Lead:'.$func,
                    'passthroughVars' => [
                        'mauticContent' => 'lead',
                        'closeModal'    => 1,
                    ],
                ]
            );
        }

        return $this->ajaxAction(
            [
                'contentTemplate' => 'MauticLeadBundle:Lead:email.html.php',
                'viewParameters'  => [
                    'form' => $form->createView(),
                    'dnc'  => end($dnc),
                ],
                'passthroughVars' => [
                    'mauticContent' => 'leadEmail',
                    'route'         => false,
                ],
            ]
        );
    }

    public function newSegAction()
    {
        if (!$this->get('mautic.security')->isGranted('lead:leads:viewown')) {
            return $this->accessDenied();
        }

        //retrieve the entity
        $list = new LeadList();
        /* @var ListModel $model */
        $model = $this->getModel('lead.list');
        //set the page we came from
        $page = $this->get('session')->get('mautic.lead.page', 1);
        //set the return URL for post actions
        $returnUrl = $this->generateUrl('mautic_contact_index');
        $action    = $this->generateUrl('mautic_contact_index');

        //get the user form factory
        $form = $model->createForm($list, $this->get('form.factory'), $action);
        ///Check for a submitted form and process it
        if ($this->request->getMethod() == 'POST') {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form) && $form->get('buttons')->get('save')->isClicked()) {
                    //form is valid so process the data
                    $model->saveEntity($list);
                    // run command to fetch contacts
                    $kernel      = $this->get('kernel');
                    $application = new Application($kernel);
                    $application->setAutoExit(false);

                    $input = new ArrayInput([
                        'command'   => 'mautic:segments:update',
                        '--list-id' => $list->getId(),
                    ]);

                    // Use the NullOutput class instead of BufferedOutput.
                    $output = new NullOutput();

                    $application->run($input, $output);

                    $this->addFlash('mautic.core.notice.created', [
                        '%name%'      => $list->getName().' ('.$list->getAlias().')',
                        '%menu_link%' => 'mautic_contact_index',
                        '%url%'       => $this->generateUrl('mautic_segment_action', [
                            'objectAction' => 'edit',
                            'objectId'     => $list->getId(),
                        ]),
                    ]);
                }
            }

            if ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked())) {
                //save with valid
                return $this->postActionRedirect([
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $page, 'search' => $cancelled ? '' : 'segment:'.$list->getAlias()],
                    'contentTemplate' => 'MauticCustomContactBundle:Lead:index',
                    'passthroughVars' => [
                        'activeLink'    => '#mautic_contact_index',
                        'mauticContent' => 'leadlist',
                    ],
                ]);
            }
            if (!$valid && $form->get('buttons')->get('save')->isClicked()) {
                //if not valid return segment
                return $this->delegateView([
                    'viewParameters' => [
                        'form' => $this->setFormTheme($form, 'MauticLeadBundle:List:form.html.php', 'MauticLeadBundle:FormTheme\Filter'),
                    ],
                    'contentTemplate' => 'MauticLeadBundle:List:form.html.php',
                    'passthroughVars' => [
                        'activeLink'    => '#mautic_segment_index',
                        'route'         => $this->generateUrl('mautic_segment_action', ['objectAction' => 'new']),
                        'mauticContent' => 'leadlist',
                    ],
                ]);
            }
            if (!$cancelled) {
                //apply
                $search = $this->getFilter($list->getFilters());

                return $this->postActionRedirect([
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $page, 'search' => $search],
                    'contentTemplate' => 'MauticCustomContactBundle:Lead:index',
                    'passthroughVars' => [
                        'activeLink'    => '#mautic_contact_index',
                        'mauticContent' => 'leadlist',
                    ],
                ]);
            }
        }

        return $this->delegateView([
            'viewParameters' => [
                'form' => $this->setFormTheme($form, 'MauticLeadBundle:List:list.html.php', 'MauticLeadBundle:FormTheme\Filter'),
            ],
            'contentTemplate' => 'MauticLeadBundle:List:form.html.php',
            'passthroughVars' => [
                'activeLink'    => '#mautic_contact_index',
                'route'         => $this->generateUrl('mautic_contact_index', ['page' => $page, 'search' => 'segment:'.$list->getAlias()]),
                'mauticContent' => 'leadlist',
            ],
        ]);
    }

    private function getFilter($filter)
    {
        $query = '';
        foreach ($filter as $f) {
            $glue = $query ? ' '.$f['glue'] : '';
            switch ($f['operator']) {
                case '!=':
                    $filter = '!('.$f['filter'].')';
                    break;
                case 'empty':
                    $filter = null;
                    break;
                case '!empty':
                    $filter = !null;
                    break;
                case 'contains':
                    $filter = '%'.$f['filter'].'%';
                    break;
                case '!like':
                    $filter = '!'.$f['filter'];
                    break;
                case 'endsWith':
                    $filter = '%'.$f['filter'];
                    break;
                case 'startsWith':
                    $filter = $f['filter'].'%';
                    break;
                default:
                    $filter = $f['filter'];
            }
            $query .= $glue.' '.$f['field'].':'.$filter;
        }

        return $query;
    }
    private function getIsPublished(){
        $integrationHelper =$this->get('mautic.helper.integration');
        $integration = $integrationHelper->getIntegrationObject('CustomContact');
        if (false === $integration || !$integration->getIntegrationSettings()->getIsPublished()) {
            return false;
        }else{
            return true;
        }
    }
}
