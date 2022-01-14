<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-qiwi-rest" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">

    <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-qiwi-rest" class="form-horizontal">

      <table width=100%>
	<tr><td width=10 valign=top>
	</td>
	<td>

          <div class="form-group required">
            <label class="col-sm-2 control-label" for="mandarinbank_ccy_select"><span data-toggle="tooltip" title="<?php echo $help_mandarinbank_ccy_select; ?>"><?php echo $entry_mandarinbank_ccy_select; ?></span></label>
            <div class="col-sm-10">
              <select name="mandarinbank_ccy_select" id="mandarinbank_ccy_select" class="form-control">
              <?php foreach ($currencies as $currency) { ?>
              <?php if ($currency['code'] == $mandarinbank_ccy_select) { ?>
              <option value="<?php echo $currency['code']; ?>" selected="selected"><?php echo $currency['code']; ?></option>
              <?php } else { ?>
              <option value="<?php echo $currency['code']; ?>"><?php echo $currency['code']; ?></option>
              <?php } ?>
              <?php } ?>
              </select>
            </div>
          </div>

          <div class="form-group required">
            <label class="col-sm-2 control-label" for="mandarinbank_shop_id"><span data-toggle="tooltip" title="<?php echo $help_mandarinbank_shop_id; ?>"><?php echo $entry_mandarinbank_shop_id; ?></span></label>
            <div class="col-sm-10">
              <input type="text" name="mandarinbank_shop_id" value="<?php echo $mandarinbank_shop_id; ?>" placeholder="<?php echo $help_mandarinbank_shop_id; ?>" id="mandarinbank_shop_id" class="form-control" />
			<?php if ($error_mandarinbank_shop_id) { ?>
              		<div class="text-danger"><?php echo $error_mandarinbank_shop_id; ?></div>
              	<?php } ?>
            </div>
          </div>

          <div class="form-group required">
            <label class="col-sm-2 control-label" for="mandarinbank_id"><span data-toggle="tooltip" title="<?php echo $help_mandarinbank_id; ?>"><?php echo $entry_mandarinbank_id; ?></span></label>
            <div class="col-sm-10">
              <input type="text" name="mandarinbank_id" value="<?php echo $mandarinbank_id; ?>" placeholder="<?php echo $help_mandarinbank_id; ?>" id="mandarinbank_id" class="form-control" />
			<?php if ($error_mandarinbank_id) { ?>
              		<div class="text-danger"><?php echo $error_mandarinbank_id; ?></div>
              	<?php } ?>
            </div>
          </div>

      <div class="form-group">
        <label class="col-sm-2 control-label" for="mandarinbank_geo_zone_id"><span data-toggle="tooltip" title="<?php echo $help_mandarinbank_geo_zone_id; ?>"><?php echo $entry_mandarinbank_geo_zone_id; ?></span></label>
        <div class="col-sm-10">
          <select name="mandarinbank_geo_zone_id" id="mandarinbank_geo_zone_id" class="form-control">
            <option value="0"><?php echo $text_all_zones; ?></option>
            <?php foreach ($geo_zones as $geo_zone) { ?>
            <?php if ($geo_zone['geo_zone_id'] == $mandarinbank_geo_zone_id) { ?>
            <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
            <?php } else { ?>
            <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
            <?php } ?>
            <?php } ?>
          </select>
        </div>
      </div>
      
      <div class="form-group">
        <label class="col-sm-2 control-label" for="mandarinbank_order_status_progress_id"><span data-toggle="tooltip" title="<?php echo $help_mandarinbank_order_status_progress_id; ?>"><?php echo $entry_mandarinbank_order_status_progress_id; ?></span></label>
        <div class="col-sm-10">
          <select name="mandarinbank_order_status_progress_id" id="mandarinbank_order_status_progress_id" class="form-control">
            <?php foreach ($order_statuses as $order_status) { ?>
            <?php if ($order_status['order_status_id'] == $mandarinbank_order_status_progress_id) { ?>
            <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
            <?php } else { ?>
            <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
            <?php } ?>
            <?php } ?>
          </select>
        </div>
      </div>
      
          <div class="form-group">
            <label class="col-sm-2 control-label" for="mandarinbank_status"><span data-toggle="tooltip" title="<?php echo $help_mandarinbank_status; ?>"><?php echo $entry_mandarinbank_status; ?></span></label>
            <div class="col-sm-10">
              <select name="mandarinbank_status" id="mandarinbank_status" class="form-control">
              <?php if ($mandarinbank_status) { ?>
              <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
              <option value="0"><?php echo $text_disabled; ?></option>
              <?php } else { ?>
              <option value="1"><?php echo $text_enabled; ?></option>
              <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
              <?php } ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="mandarinbank_sort_order"><span data-toggle="tooltip" title="<?php echo $help_mandarinbank_sort_order; ?>"><?php echo $entry_mandarinbank_sort_order; ?></span></label>
            <div class="col-sm-10">
              <input type="text" name="mandarinbank_sort_order" value="<?php echo $mandarinbank_sort_order; ?>" placeholder="<?php echo $help_mandarinbank_sort_order; ?>" id="mandarinbank_sort_order" class="form-control" />
            </div>
          </div>


        </td></tr>
      </table>



    </form>
      </div>
    </div>
<br>
		<div style="text-align:center; color:#555555;">MandarinBank v<?php echo $mandarinbank_version; ?></div>

  </div>
</div>
<?php echo $footer; ?> 