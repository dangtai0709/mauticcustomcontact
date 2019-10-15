  <!-- tabs controls -->
  <ul class="nav nav-tabs pr-md pl-md mt-10">
      <li class="active">
          <a href="#marketing-container" role="tab" data-toggle="tab">
              <?php echo $view['translator']->trans('mautic.lead.lead.tab.marketing'); ?>
          </a>
      </li>
      <li class="">
          <a href="#tele-container" role="tab" data-toggle="tab">
              <?php echo $view['translator']->trans('mautic.lead.lead.tab.tele'); ?>
          </a>
      </li>

      <li class="">
          <a href="#fc-container" role="tab" data-toggle="tab">
              <?php echo $view['translator']->trans('mautic.lead.lead.tab.FC'); ?>
          </a>
      </li>

      <?php echo $view['content']->getCustomContent('grouptabs', $mauticTemplateVars); ?>
  </ul>
  <!--/ tabs controls -->

  </div>

  <!-- start: tab-content -->
  <div class="tab-content pa-md">
      <!-- #marketing-container -->
      <div class="tab-pane fade in active bdr-w-0" id="marketing-container">

          <?php
           echo $view->render(
                    'MauticCustomContactBundle:Lead\Tabs:marketing.html.php',
                    [
                        'events' => $events,
                        'lead'   => $lead,
                        'tmpl'   => 'index',
                        'noteCount'=>$data["noteCount"],
                        'isAnonymous'=>$lead->isAnonymous(),
                        'socialProfiles'=>$data['socialProfiles'],
                        'integrations'=>$data['integrations'],
                        'auditlog'=>$data['auditlog'],
                        'places'=>$data['places'],
                        'leadNotes'=>$data['leadNotes'],
                        'socialProfileUrls'=>$data['socialProfileUrls'],
                    ]
                );
                 ?>

      </div>
      <!--/ #marketing-container -->

      <!-- #tele-container -->
      <div class="tab-pane fade bdr-w-0" id="tele-container">
          tele container
      </div>
      <!--/ #tele-container -->

      <!-- #fc-container -->

      <div class="tab-pane fade bdr-w-0" id="fc-container">
          Fc-container
      </div>

      <!--/ #fc-container -->



      <!-- custom content -->
      <?php echo $view['content']->getCustomContent('grouptabs.content', $mauticTemplateVars); ?>
      <!-- end: custom content -->
  </div>
  <!--/ end: tab-content -->