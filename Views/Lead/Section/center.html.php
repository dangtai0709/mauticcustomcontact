<div class="box-layout">
    <div class="col-md-4 bg-white  height-auto pa-lg ">
        <?php if (!$lead->isAnonymous()): ?>
        <div class="img-wrapper img-rounded mr-10 mb-sm">
            <div class="collapse in" id="lead-avatar-block">
                <img class="img-responsive" src="<?php echo $img; ?>" alt="<?php echo $view->escape($leadName); ?> " />
            </div>
        </div>
        <?php endif; ?>
        <div class="text-center">
            <h6 class="fw-sb mb-sm "><?php echo $view->escape($leadName); ?> </h6>
            <div class="mb-lg">
                <i class="fa fa-facebook-square" style="font-size:36px"></i>
                <i class="fa fa-twitter" style="font-size:36px"></i>
                <i class="fa fa-pinterest" style="font-size:36px"></i>
            </div>
        </div>
    </div>
    <div class="col-md-9 bg-white bdr-r height-auto mb-lg pt-lg">
        <?php if (isset($fields['core']['address1'])): ?>
        <h6 class="fw-sb">center
            <?php echo $view['translator']->trans('mautic.lead.field.address'); ?>
        </h6>
        <address class="text-muted">

            <?php echo $view->escape($fields['core']['address1']['value']); ?><br>

            <?php echo (!empty($fields['core']['address2']['value'])) ? $view->escape($fields['core']['address2']['value']).'<br>' : ''; ?>
            <?php echo $view->escape($lead->getLocation()); ?>
            <?php echo isset($fields['core']['zipcode']) ? $view->escape($fields['core']['zipcode']['value']) : '' ?>
        </address>
        <?php endif; ?>
        <h6 class="fw-sb"><?php echo $view['translator']->trans('mautic.core.type.email'); ?></h6>
        <p class="text-muted"><?php echo $view->escape($fields['core']['email']['value']); ?></p>

        <?php if (isset($fields['core']['phone'])): ?>
        <h6 class="fw-sb"><?php echo $view['translator']->trans('mautic.lead.field.type.tel.home'); ?></h6>
        <p class="text-muted"><?php echo $view->escape($fields['core']['phone']['value']); ?></p>
        <?php endif; ?>

        <?php if (isset($fields['core']['mobile'])): ?>
        <h6 class="fw-sb"><?php echo $view['translator']->trans('mautic.lead.field.type.tel.mobile'); ?></h6>
        <p class="text-muted mb-0"><?php echo $view->escape($fields['core']['mobile']['value']); ?></p>
        <?php endif; ?>

    </div>
</div>