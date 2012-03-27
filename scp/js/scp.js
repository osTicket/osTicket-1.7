/*
   scp.js

   osTicket SCP
   Copyright (c) osTicket.com

 */

function selectAll(formObj,task,highlight){
   var highlight = highlight || false;

   for (var i=0;i < formObj.length;i++){
      var e = formObj.elements[i];
      if (e.type == 'checkbox' && !e.disabled){
         if(task==0){
            e.checked =false;
         }else if(task==1){
            e.checked = true;
         }else{
            e.checked = (e.checked) ? false : true;
         }

         if(highlight && 0) {
            highLight(e.value,e.checked);
         }
       }
   }

   return false;
}

function reset_all(formObj){
    return selectAll(formObj,0,true);
}
function select_all(formObj,highlight){
    return selectAll(formObj,1,highlight);
}
function toogle_all(formObj,highlight){

    var highlight = highlight || false;
    return selectAll(formObj,2,highlight);
}



function checkbox_checker(formObj, min,max) {


    var checked=$("input[type=checkbox]:checked").length;
    var action= action?action:"process";
    if (max>0 && checked > max ){
        msg="You're limited to only " + max + " selections.\n"
        msg=msg + "You have made " + checked + " selections.\n"
        msg=msg + "Please remove " + (checked-max) + " selection(s)."
        alert(msg)
        return (false);
    }

    if (checked< min ){
        alert("Please make at least " + min + " selections. " + checked + " checked so far.")
        return (false);
    }

    return (true);
}


$(document).ready(function(){

    $("input:not(.dp):visible:enabled:first").focus();
    $('table.list tbody tr:odd').addClass('odd');

    if($.browser.msie) {
        $('.inactive').mouseenter(function() {
            var elem = $(this);
            var ie_shadow = $('<div>').addClass('ieshadow').css({
                height:$('ul', elem).height()
            });
            elem.append(ie_shadow);
        }).mouseleave(function() {
            $('.ieshadow').remove();
        });
    }

    $("form#save :input").change(function() {
        var fObj = $(this).closest('form');
        if(!fObj.data('changed')){
            fObj.data('changed', true);
            $('input[type=submit]', fObj).css('color', 'red');
        }
    });

    $("form#save :input[type=reset]").click(function() {
        var fObj = $(this).closest('form');
        if(fObj.data('changed')){
            $('input[type=submit]', fObj).removeAttr('style');
            $('label', fObj).removeAttr('style');
            $('label', fObj).removeClass('strike');
            fObj.data('changed', false);
        }
    });


    $(".clearrule").live('click',function() {
        $(this).closest("tr").find(":input").val('');
        return false;
     });


    //Canned attachments.
    $('#canned_attachments, #faq_attachments').delegate('input:checkbox', 'click', function(e) {
        var elem = $(this);
        if(!$(this).is(':checked') && confirm("Are you sure you want to remove this attachment?")==true) {
            elem.parent().addClass('strike');
        } else {
            elem.attr('checked', 'checked');
            elem.parent().removeClass('strike');
        }
     });

    $('form select#cannedResp').change(function() {

        var fObj=$(this).closest('form');
        var cannedId = $(this).val();
        var ticketId = $(':input[name=id]',fObj).val();

        $(this).find('option:first').attr('selected', 'selected').parent('select');

        $.ajax({
                type: "GET",
                url: 'ajax.php/kb/canned-response/'+cannedId+'.json',
                data: 'tid='+ticketId,
                dataType: 'json',
                cache: false,
                success: function(canned){
                    //Canned response.
                    if(canned.response) {
                        if($('#append',fObj).is(':checked') &&  $('#response',fObj).val())
                            $('#response',fObj).val($('#response',fObj).val()+"\n\n"+canned.response+"\n");
                        else
                            $('#response',fObj).val(canned.response);
                    }
                    //Canned attachments.
                    if(canned.files && $('#canned_attachments',fObj).length) {
                        $.each(canned.files,function(i, j) {
                            if(!$('#canned_attachments #f'+j.id,fObj).length) {
                                var file='<label><input type="checkbox" name="cannedattachments[]" value="' + j.id+'" id="f'+j.id+'" checked="checked">';
                                    file+= '<a href="file.php?h=' + j.hash + j.key+ '">'+ j.name +'</a></label>';
                                $('#canned_attachments', fObj).append(file);
                            }

                         });
                    }
                }
            })
            .done(function() { })
            .fail(function() { });
     });


    /* advanced search */

    $("#overlay").css({
        opacity : 0.3,
        top     : 0,
        left    : 0,
        width   : $(window).width(),
        height  : $(window).height()
    });

    $("#advanced-search").css({
        top  : ($(window).height() / 10),
        left : ($(window).width() / 2 - 300)
    });


    $('#go-advanced').click(function(e) {
        e.preventDefault();
        $('#overlay').show(function() {
            $('#advanced-search').show();
        });
    });

    /* ------ */

    /* global inits */

    /* Get config settings from the backend */
    $.get('ajax.php/config/ui.json',
        function(config){
            /*
            if(config && config.max_attachments)
                alert(config.max_attachments);
            */
        },
        'json')
        .error( function() {});

    /* NicEdit richtext init */
    var rtes = $('.richtext');
    var rtes_count = rtes.length;
    for(i=0;i<rtes_count;i++) {
        var initial_value = rtes[i].value;
        rtes[i].id = 'rte-'+i;
        new nicEditor({iconsPath:'images/nicEditorIcons.gif'}).panelInstance('rte-'+i);
        if(initial_value=='') {
            nicEditors.findEditor('rte-'+i).setContent('');
        }
    }

});
