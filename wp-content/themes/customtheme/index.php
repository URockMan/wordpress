<?php /* Template Name: CustomPageT1 */
include_once 'CustomPageTheme.php';

$custom = new CustomPageTheme();
$domain_output = $custom->getInstalledComponents();
$myJSON = json_encode($domain_output);
/*  echo "<pre>";
print_r($domain_output); */

?>
<script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.21/js/dataTables.jqueryui.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/rowgroup/1.1.2/js/dataTables.rowGroup.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>

<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/dataTables.jqueryui.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/rowgroup/1.1.2/css/rowGroup.jqueryui.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
<link rel="stylesheet" href="<?php echo get_template_directory_uri().'/style.css';?>">



<script type="text/javascript">
var theme_path = "<?php echo get_template_directory_uri();?>";
var table_data = <?php echo $myJSON;?>;
//{"current_version":"5.7.22-log","status":"Active","is_update":"--","new_version":"--"}
var final_list = [];
var drop_down_list = ['Select'];
$.each(table_data, function(key,obj) {
    drop_down_list.push(key);
    $.each(obj, function(k1,v1) {
        var list = [];
       
        $.each(v1, function(k,v) {
            //alert(v.current_version);
            //console.log(v);
            list = [k1,k,v.current_version,v.history,v.status,v.is_update,v.new_version];
            final_list.push(list);
        });
    });
});

$(document).ready(function() {
    var tableDomain = reloadDataTable(final_list);
    /* $(document).on("change", "#domain_dropdown", function () {
        $('#datepicker').val('');
		$('#timepicker').val('');
        var truncated_list = [];
        table_data = <?php echo $myJSON;?>;
        $.each(table_data, function(key,obj) {
            if($.trim(key).indexOf($('#domain_dropdown').val()) != -1 || $('#domain_dropdown').val() == 'Select')
            {
                $.each(obj, function(k1,v1) {
                var list = [];
                    $.each(v1, function(k,v) {
                        list = [k1,k,v.current_version,v.history,v.status,v.is_update,v.new_version];
                        truncated_list.push(list);
                    });
                });
            }
        });
        //console.log(truncated_list);
        $('#example').dataTable().fnDestroy();
        tableDomain = null;;
        tableDomain = reloadDataTable(truncated_list);
    }); */
   
     var availableTags = [
      "as-cv-pub-vip-vol-wl-p-001",
      "a994f071d7ed3401cb152b1df46fc9d3-1471600098",
      "a23a9268ca049478c8bd5eca799ccd26-1829171198",
      "a11417d24790447be8109179cb9f739d-1862852182",
      "ae691f9ef93674b62871a86b2a37477e-166837189",
      "a98dbf1aba9f14f63a456a755ad33202-1759781684",
      "localhost"
    ];
    $( "#domain_dropdown" ).autocomplete({
      source: availableTags,
	  select: function( event, ui ) {
		  $('#datepicker').val('');
		$('#timepicker').val('');
        var truncated_list = [];
        table_data = <?php echo $myJSON;?>;
        $.each(table_data, function(key,obj) {
            if($.trim(key).indexOf($('#domain_dropdown').val()) != -1 || $('#domain_dropdown').val() == 'Select')
            {
                $.each(obj, function(k1,v1) {
                var list = [];
                    $.each(v1, function(k,v) {
                        list = [k1,k,v.current_version,v.history,v.status,v.is_update,v.new_version];
                        truncated_list.push(list);
                    });
                });
            }
        });
        //console.log(truncated_list);
        $('#example').dataTable().fnDestroy();
        tableDomain = null;;
        tableDomain = reloadDataTable(truncated_list);
	  }
    });
   
   
} );

function reloadDataTable(data_list) {
$('#example').DataTable( {
        order: [[0, 'desc']],
        pageLength: 50,
        rowGroup: {
            dataSrc: 0
        },
        data: data_list,
        rowCallback: function(row, data, index){
            if(data[3] != 'No Changes'){
                $(row).find('td:eq(3)').css('color', 'red');
            }
          }
    } );    
}
</script>
<script type="text/javascript" src="<?php echo get_template_directory_uri().'/js/main.js';?>"></script>


<div class="utility-header hidden-print">
<img style="height: 40px;" src="<?php echo get_template_directory_uri().'/images/header.png';?>" />
</div>
<br/><br/>  
<label for="domain">Choose a Domain: </label><input id="domain_dropdown" type="text" />    
<!--<label for="domain">Choose a Domain:</label>

 <select name="domains" id="domain_dropdown">
  <option value="select">Select</option>
  <option value="as-cv-pub-vip-vol-wl-p-001">as-cv-pub-vip-vol-wl-p-001</option>
  <option value="a994f071d7ed3401cb152b1df46fc9d3-1471600098">a994f071d7ed3401cb152b1df46fc9d3-1471600098</option>
  <option value="a23a9268ca049478c8bd5eca799ccd26-1829171198">a23a9268ca049478c8bd5eca799ccd26-1829171198</option>
  <option value="a11417d24790447be8109179cb9f739d-1862852182">a11417d24790447be8109179cb9f739d-1862852182</option>
  <option value="localhost">localhost</option>
</select>-->
<label for="date">Select Date:</label> <input id="datepicker" type="text" >
<label for="date_time">Select Time:</label> <input id="timepicker" type="text" />

<!--<label for="date_time">Select Date Time:</label> <input id="datetimepicker" type="text" >-->
<input id="search" type="button" value='Search'/>
<input id="reset" type="button" value='Reset'/>
<!-- Image loader -->
<div id='loader' style='display: none;float:right;padding-right:600px;'>
  <img src="<?php echo get_template_directory_uri().'/images/ajax-loader.gif';?>" width='32px' height='32px'>
</div>
<!-- Image loader -->


<!--<select name="domains" id="domains">
  <option value="select">Select</option>
  <option value="localhost">localhost</option>
</select> -->
<br/><br/>
<table id="example" class="display" style="width:100%">
        <thead>
            <tr>
                <th>Info</th>
                <th>Software</th>
                <th>Current version</th>
                <th>History</th>
                <th>Status</th>
                <th>Is update Available</th>
                <th>New version</th>
            </tr>
        </thead>
        <tbody>
        <?php
             
            foreach ( $domain_output as $domain => $site_output ) {
                foreach ( $site_output as $key => $value ) {
                     foreach ( $value as $k => $v ) {
                    echo "<tr>
                       
                        <td>".$key."</td>
                        <td nowrap>".$k."</td>
                        <td nowrap>".$v['current_version']."</td>
                        <td nowrap>".$v['history']."</td>
                        <td wrap>".$v['status']."</td>
                        <td wrap>".$v['is_update']."</td>
                        <td wrap>".$v['new_version']."</td>
                    </tr>";
                     }
                }
            }
         ?>
        </tbody>
    </table>
<div >
<img style="height: 30px;width:100%" src="<?php echo get_template_directory_uri().'/images/footer.png';?>" />
</div>