<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
if ($tmpl == 'index') {
    $view->extend('MauticCustomContactBundle:Lead:index.html.php');
}

$customButtons = [];
if ($permissions['lead:leads:editown'] || $permissions['lead:leads:editother']) {
    $customButtons = [
        [
            'attr' => [
                'class'       => 'btn btn-default btn-sm btn-nospin',
                'data-toggle' => 'ajaxmodal',
                'data-target' => '#MauticSharedModal',
                'href'        => $view['router']->path('mautic_segment_batch_contact_view'),
                'data-header' => $view['translator']->trans('mautic.lead.batch.lists'),
            ],
            'btnText'   => $view['translator']->trans('mautic.lead.batch.lists'),
            'iconClass' => 'fa fa-pie-chart',
        ],
        [
            'attr' => [
                'class'       => 'btn btn-default btn-sm btn-nospin',
                'data-toggle' => 'ajaxmodal',
                'data-target' => '#MauticSharedModal',
                'href'        => $view['router']->path('mautic_contact_action', ['objectAction' => 'batchStages']),
                'data-header' => $view['translator']->trans('mautic.lead.batch.stages'),
            ],
            'btnText'   => $view['translator']->trans('mautic.lead.batch.stages'),
            'iconClass' => 'fa fa-tachometer',
        ],
        [
            'attr' => [
                'class'       => 'btn btn-default btn-sm btn-nospin',
                'data-toggle' => 'ajaxmodal',
                'data-target' => '#MauticSharedModal',
                'href'        => $view['router']->path('mautic_contact_action', ['objectAction' => 'batchCampaigns']),
                'data-header' => $view['translator']->trans('mautic.lead.batch.campaigns'),
            ],
            'btnText'   => $view['translator']->trans('mautic.lead.batch.campaigns'),
            'iconClass' => 'fa fa-clock-o',
        ],
        [
            'attr' => [
                'class'       => 'btn btn-default btn-sm btn-nospin',
                'data-toggle' => 'ajaxmodal',
                'data-target' => '#MauticSharedModal',
                'href'        => $view['router']->path('mautic_contact_action', ['objectAction' => 'batchOwners']),
                'data-header' => $view['translator']->trans('mautic.lead.batch.owner'),
            ],
            'btnText'   => $view['translator']->trans('mautic.lead.batch.owner'),
            'iconClass' => 'fa fa-user',
        ],
        [
            'attr' => [
                'class'       => 'btn btn-default btn-sm btn-nospin',
                'data-toggle' => 'ajaxmodal',
                'data-target' => '#MauticSharedModal',
                'href'        => $view['router']->path('mautic_contact_action', ['objectAction' => 'batchDnc']),
                'data-header' => $view['translator']->trans('mautic.lead.batch.dnc'),
            ],
            'btnText'   => $view['translator']->trans('mautic.lead.batch.dnc'),
            'iconClass' => 'fa fa-ban text-danger',
        ],
    ];
}
$fields = $form->vars['fields'];
$index  = count($form['filters']->vars['value']) ? max(array_keys($form['filters']->vars['value'])) : 0;
echo $view['assets']->includeScript('plugins/MauticCustomContactBundle/Assets/js/EtLeadBundle.js');
echo $view['assets']->includeStylesheet('plugins/MauticCustomContactBundle/Assets/css/EtLeadBundle.css');
?>
<script>
mQuery(document).ready(function() {
    mQuery(".toolbar-form-buttons.pull-right").hide()
});
</script>

<?php if (count($items)): ?>
<div class="box-layout">
    <div class="col-md-2 bg-white height-auto">
        <div class="pr-lg pl-lg pt-md pb-md">
            <div class="row">
                <div class="form-group col-xs-12 ">
                    <label class="control-label ">All contacts</label>
                    <div>
                        <?php echo  $totalItems?> items,
                        <?php echo isset($limit) ? (int) ceil($totalItems / $limit) : 1?>
                        pages in total
                    </div>
                </div>
            </div>
            <!--  -->
            <div class="">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <span class="panel-title">
                            <a data-toggle="collapse" href="#collapse1">All saved filters</a>
                        </span>
                    </div>
                    <div id="collapse1" class="panel-collapse collapse">
                        <ul class="list-group">
                            <?php 
                        if (count($filters)):
                        foreach ($filters as $f):''?>
                            <?php if ($f->isPublished()):?>
                            <a href="<?php echo $view['router']->path(
                            'mautic_contact_index',
                            ['search' => $view['translator']->trans('mautic.lead.lead.searchcommand.list').':'.$f->getAlias()]
                        ); ?>" data-toggle="ajax">
                                <li class="list-group-item">
                                    <?php echo $f->getName(); ?>
                                </li>
                            </a>
                            <?php endif; ?>
                            <?php endforeach;
                            else:?>
                            <li class="list-group-item">Nothing</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <!--  -->
            <a class="btn btn-default" data-toggle="ajax" onclick="mQuery('#search').show();"><span
                    data-toggle="tooltip" title="" data-placement="left"
                    data-original-title="If you upload a CSV contact file to a segment, it will become a static list. Applying additional filters to this segment will not remove any contacts from that uploaded list."><i
                        class="fa fa-plus"></i> <span class="hidden-xs hidden-sm">Add filter</span></span></a>
            <!--  -->
            <?php echo $view['form']->start($form); ?>
            <div class="form-group pt-md " id="search" style="display:none">
                <?php echo $view['form']->row($form['name']); ?>
                <?php echo $view['form']->row($form['buttons']); ?>                             
                <div class="toolbar-form-buttons">
                    <div class="segment-button btn-group toolbar-standard hidden-xs hidden-sm"></div>
                    <div class="btn-group toolbar-dropdown hidden-md hidden-lg">
                    </div>
                </div>
                <div class="available-filters mb-md pl-0 col-md-12"
                    data-prototype="<?php echo $view->escape($view['form']->widget($form['filters']->vars['prototype'])); ?>"
                    data-index="<?php echo $index + 1; ?>">
                    <select class="chosen form-control" id="available_filters">
                        <option value=""></option>
                        <?php
                                    foreach ($fields as $object => $field):
                                        $header = $object;
                                        $icon   = ($object == 'company') ? 'building' : 'user';
                                    ?>
                        <optgroup label="<?php echo $view['translator']->trans('mautic.lead.'.$header); ?>">
                            <?php foreach ($field as $value => $params):
                                            $list      = (!empty($params['properties']['list'])) ? $params['properties']['list'] : [];
                                            $choices   = \Mautic\LeadBundle\Helper\FormFieldHelper::parseList($list, true, ('boolean' === $params['properties']['type']));
                                            $list      = json_encode($choices);
                                            $callback  = (!empty($params['properties']['callback'])) ? $params['properties']['callback'] : '';
                                            $operators = (!empty($params['operators'])) ? $view->escape(json_encode($params['operators'])) : '{}';
                                            ?>
                            <option value="<?php echo $view->escape($value); ?>"
                                id="available_<?php echo $object.'_'.$value; ?>"
                                data-field-object="<?php echo $object; ?>"
                                data-field-type="<?php echo $params['properties']['type']; ?>"
                                data-field-list="<?php echo $view->escape($list); ?>"
                                data-field-callback="<?php echo $callback; ?>"
                                data-field-operators="<?php echo $operators; ?>"
                                class="segment-filter <?php echo $icon; ?>">
                                <?php echo $view['translator']->trans($params['label']); ?>
                            </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="selected-filters" id="leadlist_filters">

                <?php echo $view['form']->widget($form['filters']); ?>
            </div>
            <div class="hidden">
                <?php echo $view['form']->row($form['isGlobal']); ?>
                <?php echo $view['form']->row($form['isPreferenceCenter']); ?>
                <?php echo $view['form']->row($form['isPublished']); ?>
                <?php echo $view['form']->row($form['alias']); ?>
                <?php echo $view['form']->row($form['description']); ?>

            </div>
            <?php echo $view['form']->end($form); ?>
            <!--  -->
        </div>
    </div>
    <div class="col-md-10 bg-auto height-auto bdr-r">
        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered" id="leadTable">
                <thead>
                    <tr>
                        <?php
                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'checkall'        => 'true',
                    'target'          => '#leadTable',
                    'templateButtons' => [
                        'delete' => $permissions['lead:leads:deleteown'] || $permissions['lead:leads:deleteother'],
                    ],
                    'customButtons' => $customButtons,
                    'langVar'       => 'lead.lead',
                    'routeBase'     => 'contact',
                    'tooltip'       => $view['translator']->trans('mautic.lead.list.checkall.help'),
                ]);

                $columsAliases = array_flip($columns);
                foreach ($columns as $column=>$label) {
                    $template = 'MauticCustomContactBundle:Lead\header:'.$column.'.html.php';
                    if (!$view->exists($template)) {
                        $template = 'MauticCustomContactBundle:Lead\header:default.html.php';
                    }
                    echo $view->render(
                        $template,
                        [
                            'label'  => $label,
                            'column' => $column,
                            'class'  => array_search($column, $columsAliases) > 1 ? 'hidden-xs' : '',
                        ]
                    );
                }
                ?>
                    </tr>
                </thead>
                <tbody>
                    <?php echo $view->render('MauticCustomContactBundle:Lead:list_rows.html.php', [
            'items'         => $items,
            'columns'       => $columns,
            'security'      => $security,
            'currentList'   => $currentList,
            'permissions'   => $permissions,
            'noContactList' => $noContactList,
        ]); ?>
                </tbody>
            </table>
        </div>
        <div class="panel-footer">
            <?php echo $view->render('MauticCoreBundle:Helper:pagination.html.php', [
        'totalItems' => $totalItems,
        'page'       => $page,
        'limit'      => $limit,
        'menuLinkId' => 'mautic_contact_index',
        'baseUrl'    => $view['router']->path('mautic_contact_index'),
        'tmpl'       => $indexMode,
        'sessionVar' => 'lead',
    ]); ?>
        </div>
    </div>
</div>
<?php else: ?>
<?php echo $view->render('MauticCoreBundle:Helper:noresults.html.php'); ?>
<?php endif; ?>