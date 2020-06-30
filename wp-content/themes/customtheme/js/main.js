$(document).ready(function() {
    

    jQuery( "#datepicker" ).datepicker(
    {
     dateFormat: 'yy-mm-dd'}
    );

/*     $('#datepicker').change(function() {
        console.log($(this).val());
        item = {}
        item ["domain"] = $('#domain_dropdown').val();
        item ["date"] = $(this).val();

        $.ajax({
            url: theme_path+"/post.php",
            type: "post",
            data: item ,
            success: function (response) {
                console.log(response.length);
                
                var table_data = JSON.parse(response);
                var truncated_list = [];
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
               // You will get response from your PHP page (what you echo or print)
            },
            error: function(jqXHR, textStatus, errorThrown) {
               console.log(textStatus, errorThrown);
            }
        });
    
    }); */ 
    
    $('#reset').click(function() {
        //console.log(table_data);
        $('#timepicker').val('');
        $('#datepicker').val('');
        $('#domain_dropdown').val('select');
        item = {}
        $.ajax({
            url: theme_path+"/post.php",
            type: "post",
            data: item ,
            beforeSend: function(){
            // Show image container
            $("#loader").show();
           },
            success: function (response) {
                //console.log(response.length);
                
                var table_data = JSON.parse(response);
                var truncated_list = [];
                $.each(table_data, function(key,obj) {
                        $.each(obj, function(k1,v1) {
                        var list = [];
                            $.each(v1, function(k,v) {
                                list = [k1,k,v.current_version,v.history,v.status,v.is_update,v.new_version];
                                truncated_list.push(list);
                            }); 
                        });
                });
            console.log(truncated_list);
            $('#example').dataTable().fnDestroy();
            tableDomain = null;;
            tableDomain = reloadDataTable(truncated_list);
               // You will get response from your PHP page (what you echo or print)
            },
            error: function(jqXHR, textStatus, errorThrown) {
               console.log(textStatus, errorThrown);
            },
            complete:function(data){
    // Hide image container
    $("#loader").hide();
   }
        });
    });

    

    var now = new Date(Date.now());
var formatted = now.getHours() + ":" + now.getMinutes();
    $( "#datetimepicker" ).datetimepicker({
        maxDate: '-1970/01/02',
        formatDate: 'yy-mm-dd',
        stepMinute: 30,
        step: 30
         
    });
     $('#timepicker').timepicker({
        timeFormat: 'HH:mm' 
     });
    $('#search').click(function() {
        
        item = {}
        item["domain"] = $('#domain_dropdown').val();
       /*  utcDate = new Date($('#datetimepicker').val()).toUTCString();
        item["datetime"] = utcDate;
        console.log(utcDate); */
        
        if($('#datepicker').val() == ''){
            alert('Select Date to fetch history..');
            return;
        }
        if($('#timepicker').val() != '')
        {
            var localDate = $('#datepicker').val()+' '+$('#timepicker').val();
            utcDate = new Date(localDate).toUTCString();
            item["datetime"] = utcDate;
            console.log(utcDate);
        }
        else {
            item ["date"] = $('#datepicker').val();
        }
        //return;
        $.ajax({
            url: theme_path+"/post.php",
            type: "post",
            data: item ,
            beforeSend: function(){
            // Show image container
            $("#loader").show();
           },
            success: function (response) {
                console.log(response.length);
                
                var table_data = JSON.parse(response);
                var truncated_list = [];
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
               // You will get response from your PHP page (what you echo or print)
            },
            error: function(jqXHR, textStatus, errorThrown) {
               console.log(textStatus, errorThrown);
            },
            complete:function(data){
    // Hide image container
    $("#loader").hide();
   }
        });
    
    });
});
