<!-- tabs controls -->
<div>
    <ul class="nav nav-tabs pr-md pl-md mt-10">
        <li class="active">
            <a href="#timeline-container" role="tab" data-toggle="tab">
                <span class="label label-primary mr-sm" id="TimelineCount">
                    <?php echo $events['total']; ?>
                </span>
                <?php echo $view['translator']->trans('mautic.lead.lead.tab.history'); ?>
            </a>
        </li>
        <li class="">
            <a href="#notes-container" role="tab" data-toggle="tab">
                <span class="label label-primary mr-sm" id="NoteCount">
                    <?php echo $noteCount; ?>
                </span>
                <?php echo $view['translator']->trans('mautic.lead.lead.tab.notes'); ?>
            </a>
        </li>
        <?php if (!$isAnonymous): ?>
        <li class="">
            <a href="#social-container" role="tab" data-toggle="tab">
                <span class="label label-primary mr-sm" id="SocialCount">
                    <?php echo count($socialProfiles); ?>
                </span>
                <?php echo $view['translator']->trans('mautic.lead.lead.tab.social'); ?>
            </a>
        </li>
        <?php endif; ?>
        <li class="">
            <a href="#integration-container" role="tab" data-toggle="tab">
                <span class="label label-primary mr-sm" id="IntegrationCount">
                    <?php echo count($integrations); ?>
                </span>
                <?php echo $view['translator']->trans('mautic.lead.lead.tab.integration'); ?>
            </a>
        </li>
        <li class="">
            <a href="#auditlog-container" role="tab" data-toggle="tab">
                <span class="label label-primary mr-sm" id="AuditLogCount">
                    <?php echo $auditlog['total']; ?>
                </span>
                <?php echo $view['translator']->trans('mautic.lead.lead.tab.auditlog'); ?>
            </a>
        </li>
        <?php if ($places): ?>
        <li class="">
            <a href="#place-container" role="tab" data-toggle="tab" id="load-lead-map">
                <span class="label label-primary mr-sm" id="PlaceCount">
                    <?php echo count($places); ?>
                </span>
                <?php echo $view['translator']->trans('mautic.lead.lead.tab.places'); ?>
            </a>
        </li>
        <?php endif; ?>

        <?php echo $view['content']->getCustomContent('tabs', $mauticTemplateVars); ?>
    </ul>
    <!--/ tabs controls -->

</div>

<!-- start: tab-content -->
<div class="tab-content pa-md">
    <!-- #history-container -->
    <div class="tab-pane fade in active bdr-w-0" id="timeline-container">
        <?php echo $view->render(
                    'MauticLeadBundle:Timeline:list.html.php',
                    [
                        'events' => $events,
                        'lead'   => $lead,
                        'tmpl'   => 'index',
                    ]
                ); ?>
    </div>
    <!--/ #history-container -->

    <!-- #notes-container -->
    <div class="tab-pane fade bdr-w-0" id="notes-container">
        <?php echo $leadNotes; ?>
    </div>
    <!--/ #notes-container -->

    <!-- #social-container -->
    <?php if (!$isAnonymous): ?>
    <div class="tab-pane fade bdr-w-0" id="social-container">
        <?php echo $view->render(
                        'MauticLeadBundle:Social:index.html.php',
                        [
                            'lead'              => $lead,
                            'socialProfiles'    => $socialProfiles,
                            'socialProfileUrls' => $socialProfileUrls,
                        ]
                    ); ?>
    </div>
    <?php endif; ?>
    <!--/ #social-container -->

    <!-- #integration-container -->
    <div class="tab-pane fade bdr-w-0" id="integration-container">
        <?php echo $view->render(
                    'MauticLeadBundle:Integration:index.html.php',
                    [
                        'lead'         => $lead,
                        'integrations' => $integrations,
                    ]
                ); ?>
    </div>
    <!--/ #integration-container -->

    <!-- #auditlog-container -->
    <div class="tab-pane fade bdr-w-0" id="auditlog-container">
        <?php echo $view->render(
                    'MauticLeadBundle:Auditlog:list.html.php',
                    [
                        'events' => $auditlog,
                        'lead'   => $lead,
                        'tmpl'   => 'index',
                    ]
                ); ?>
    </div>
    <!--/ #auditlog-container -->

    <!-- custom content -->
    <?php echo $view['content']->getCustomContent('tabs.content', $mauticTemplateVars); ?>
    <!-- end: custom content -->

    <!-- #place-container -->
    <?php if ($places): ?>
    <div class="tab-pane fade bdr-w-0" id="place-container">
        <?php echo $view->render('MauticLeadBundle:Lead:map.html.php', ['places' => $places]); ?>
    </div>
    <?php endif; ?>
    <!--/ #place-container -->
</div>

<!--/ end: tab-content -->