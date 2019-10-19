<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$view->extend('MauticCustomContactBundle:Default:content.html.php');
$view['slots']->set('mauticContent', 'lead');
$view['slots']->set('headerTitle', $view['translator']->trans('mautic.lead.leads'));

$pageButtons = [];
if ($permissions['lead:leads:create']) {

    if ($permissions['lead:imports:create']) {
        $pageButtons[] = [
            'attr' => [
                'class'       => 'btn btn-default btn-import-contact',
                'href'        => $view['router']->path('mautic_import_action', ['object' => 'contacts', 'objectAction' => 'new']),
            ],
            'btnText'   => 'mautic.lead.lead.import',
            'primary'   => true,
        ];
    }

}

// Only show toggle buttons for accessibility
$extraHtml = <<<button
<div class="btn-group ml-5 sr-only ">
    <span data-toggle="tooltip" title="{$view['translator']->trans(
    'mautic.lead.tooltip.list'
)}" data-placement="left"><a id="table-view" href="{$view['router']->path('mautic_contact_index', ['page' =>$page, 'view' => 'list'])}" data-toggle="ajax" class="btn btn-default"><i class="fa fa-fw fa-table"></i></span></a>
    <span data-toggle="tooltip" title="{$view['translator']->trans(
    'mautic.lead.tooltip.grid'
)}" data-placement="left"><a id="card-view" href="{$view['router']->path('mautic_contact_index', ['page' =>$page, 'view' => 'grid'])}" data-toggle="ajax" class="btn btn-default"><i class="fa fa-fw fa-th-large"></i></span></a>
</div>
button;

$view['slots']->set(
    'actions',
    $view->render(
        'MauticCustomContactBundle:Helper:page_actions.html.php',
        [
            'templateButtons' => [
                'new' => $permissions['lead:leads:create'],
            ],
            'routeBase'     => 'contact',
            'langVar'       => 'lead.lead',
            'customButtons' => $pageButtons,
            'extraHtml'     => $extraHtml,
        ]
    )
);

$toolbarButtons = [
    [
        'attr' => [
            'class'       => 'hidden-xs btn btn-default btn-sm btn-nospin',
            'href'        => 'javascript: void(0)',
            'onclick'     => 'Mautic.toggleLiveLeadListUpdate();',
            'id'          => 'liveModeButton',
            'data-toggle' => false,
            'data-max-id' => $maxLeadId,
        ],
        'tooltip'   => $view['translator']->trans('mautic.lead.lead.live_update'),
        'iconClass' => 'fa fa-bolt',
    ],
];

if ($indexMode == 'list') {
    $toolbarButtons[] = [
        'attr' => [
            'class'          => 'hidden-xs btn btn-default btn-sm btn-nospin'.(($anonymousShowing) ? ' btn-primary' : ''),
            'href'           => 'javascript: void(0)',
            'onclick'        => 'Mautic.toggleAnonymousLeads();',
            'id'             => 'anonymousLeadButton',
            'data-anonymous' => $view['translator']->trans('mautic.lead.lead.searchcommand.isanonymous'),
        ],
        'tooltip'   => $view['translator']->trans('mautic.lead.lead.anonymous_leads'),
        'iconClass' => 'fa fa-user-secret',
    ];
}

$view['slots']->set(
    'list_toolbar_header_contact',
    $view->render(
        'MauticCustomContactBundle:Helper:list_toolbar.html.php',
        [
            'searchValue'   => $searchValue,
            'searchHelp'    => 'mautic.lead.lead.help.searchcommands',
            'action'        => $currentRoute,
            'customButtons' => $toolbarButtons,
        ]
    )
);
?>
<div class="panel panel-default bdr-t-wdh-0 mb-0">
    <div id='list-status'>
        <ul>
            <li><a href="javascript: void(0);" class="status font-weight-bold">Status: </a></li>
            <li><a href="javascript: void(0);" data-status='' data-route='<?= $currentRoute ?>' )" class="status-all">All</a></li>
            <?php foreach ($listStatus as $status) {
    ?>
                <li><a href="javascript: void(0);" data-status='status:<?= $status['value'] ?>' data-route='<?= $currentRoute ?>' )" class="status-<?= $status['value'] ?>"><?= $status['label'] ?></a></li>
            <?php
} ?>
            <li><a href="<?= $urlAddStatus ?>" class="status-add_tab">Add Tab</a></li>
        </ul>
    </div>
    <div class="page-list">
        <?php $view['slots']->output('_content'); ?>
    </div>
</div>