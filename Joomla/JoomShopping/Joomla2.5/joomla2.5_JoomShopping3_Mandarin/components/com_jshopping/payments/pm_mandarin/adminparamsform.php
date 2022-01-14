<?php defined('_JEXEC') or die(); ?>
<div class="col100">
<fieldset class="adminform">
<table class="admintable" width = "100%" >
 <tr>
   <td style="width:250px;" class="key">Merchant ID</td>
   <td>
     <input type = "text" class = "inputbox" name = "pm_params[mid]" size="45" value = "<?php echo $params['mid']?>" />
   </td>
 </tr>
 <tr>
   <td style="width:250px;" class="key">Merchant Secret</td>
   <td>
     <input type = "text" class = "inputbox" name = "pm_params[msec]" size="45" value = "<?php echo $params['msec']?>" />
   </td>
 </tr> 
</table>
</fieldset>
</div>
<div class="clr"></div>