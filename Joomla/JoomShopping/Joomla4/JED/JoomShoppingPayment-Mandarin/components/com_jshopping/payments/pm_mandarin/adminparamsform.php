<?php

defined('_JEXEC') or die();

?><div class="col100">
<fieldset class="adminform">
<table class="admintable" width = "100%" >
 <tr>
   <td  class="key">Merchant ID</td>
   <td>
     <input type = "text" class = "inputbox" name = "pm_params[mid]" size="45" value = "<?php echo $params['mid']?>" />
   </td>
 </tr>
 <tr>
   <td  class="key">Merchant Secret</td>
   <td>
     <input type = "text" class = "inputbox" name = "pm_params[msec]" size="45" value = "<?php echo $params['msec']?>" />
   </td>
 </tr>
 <tr>
   <td class="key">
      <?php echo "Status Success"; ?>
   </td>
   <td>
     <?php              
         print JHTML::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[transaction_end_status]', 'class = "inputbox" size = "1"', 'status_id', 'name', $params['transaction_end_status'] );
     ?>
   </td>
 </tr>
 <tr>
     <td class="key">
      <?php echo "Status Pending"; ?>
   </td>
   <td>
     <?php 
         echo JHTML::_('select.genericlist',$orders->getAllOrderStatus(), 'pm_params[transaction_pending_status]', 'class = "inputbox" size = "1"', 'status_id', 'name', $params['transaction_pending_status']);
     ?>
   </td>
 </tr>
 <tr>
     <td class="key">
      <?php echo "Status Canceled"; ?>
   </td>
   <td>
     <?php 
     echo JHTML::_('select.genericlist',$orders->getAllOrderStatus(), 'pm_params[transaction_failed_status]', 'class = "inputbox" size = "1"', 'status_id', 'name', $params['transaction_failed_status']);
     ?>
   </td>
 </tr> 
</table>
</fieldset>
</div>
<div class="clr"></div>