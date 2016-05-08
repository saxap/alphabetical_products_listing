<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-featured" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
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
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $settxt; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-AlphabeticallyProducts" class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-name"><?php echo $title; ?></label>
            <div class="col-sm-10">
              <input type="text" name="name" value="<?php echo $name; ?>" id="input-name" class="form-control" />
            </div>
          </div>          


          <div class="form-group">
            <label class="col-sm-2 control-label" for="lat"><?php echo $lattxt; ?></label>
            <div class="col-sm-10">
              <label class="checkbox-inline">
                <input type="checkbox" value="1" name="lat" <?php echo $lat == 1 ? 'checked' : ''; ?>>
              </label>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="cyr"><?php echo $cyrtxt; ?></label>
            <div class="col-sm-10">
              <label class="checkbox-inline">
                <input type="checkbox" value="1" name="cyr" <?php echo $cyr == 1 ? 'checked' : ''; ?>>
              </label>
            </div>
          </div>


          <div class="form-group">
            <label class="col-sm-2 control-label" for="only"><?php echo $onlytxt; ?></label>
            <div class="col-sm-10">
              <label class="checkbox-inline">
                <input type="checkbox" value="1" name="only" <?php echo $only == 1 ? 'checked' : ''; ?>>
              </label>
            </div>
          </div>


          <div class="form-group">
            <label class="col-sm-2 control-label" for="num"><?php echo $numtxt; ?></label>
            <div class="col-sm-10">
              <label class="checkbox-inline">
                <input type="checkbox" value="1" name="num" <?php echo $num == 1 ? 'checked' : ''; ?>>
              </label>
            </div>
          </div>


          <div class="form-group">
            <label class="col-sm-2 control-label" for="line"><?php echo $linetxt; ?></label>
            <div class="col-sm-10">
              <label class="checkbox-inline">
                <input type="checkbox" value="1" name="line" <?php echo $line == 1 ? 'checked' : ''; ?>>
              </label>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="from_current"><?php echo $from_currenttxt; ?></label>
            <div class="col-sm-10">
              <label class="checkbox-inline">
                <input type="checkbox" value="1" name="from_current" <?php echo $from_current == 1 ? 'checked' : ''; ?>>
              </label>
              <span class="help-block"><?php echo $from_currenttxt2; ?></span>
            </div>
          </div>         

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="status" id="input-status" class="form-control">
                <?php if ($status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>

        </form>
        <p class="text-right"><?php echo $copy; ?></p>
      </div>

    </div>
  </div>
</div>
<?php echo $footer; ?>
