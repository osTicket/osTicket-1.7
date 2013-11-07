<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die(lang('access_denied'));
$info=array();
$title=lang('editing_language');
$action='update';
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
$language =(isset($_REQUEST['key']) ? $_REQUEST['key'] : '' );
$qstr='key='.$language;
?>

<script type="text/javascript">
    $(document).ready(function(){
        var CurrentPage=0;
        var elementcount=$('#content_table tr:not(.fixed)').length;

        countPages=function()
        {
            count = elementcount/parseInt($('#element-count').val());
            if(count>parseInt(count,0))
                count=parseInt(count,0)+1;

            $('#total-pages').html(count);
        }

        Paginate=function(PagePosition)
        {
            PagePosition--;
            var ElementNumber=parseInt($('#element-count').val());
            var TotalPages=parseInt($('#total-pages').html());
            $('#pg-container div>span').removeClass('disabled');

            if(PagePosition<=0)
            {
                ElementNumber++;
                PagePosition=0;
                $('.seek-first,.seek-prev').addClass('disabled');
            }

            if(PagePosition+1>=TotalPages)
            {
                PagePosition=TotalPages-1;
                $('.seek-next,.seek-end').addClass('disabled');
            }

            $('#content_table tr:not(.fixed)').css('display','none');
            for (var i=PagePosition*ElementNumber; i < (PagePosition*ElementNumber)+ElementNumber ; i++) {
                $('#content_table tr:eq('+i.toString()+')').css('display','block');
                firstTD=true;
                $('#content_table tr:eq('+i.toString()+'):not(.fixed) td').each(function(){
                    if(firstTD)
                        $(this).css('width','300px');
                    else
                        $(this).css('width','635px');

                    firstTD=false;
                })
            };

            CurrentPage=PagePosition+1;
            $('#current-page').val(CurrentPage);
        };

        $('.seek-first:not(.disabled)').click(function(){
            Paginate(1);
        });

        $('.seek-prev:not(.disabled)').click(function(){
            Paginate(CurrentPage-1);
        });

        $('.seek-next:not(.disabled)').click(function(){
            Paginate(CurrentPage+1);
        });

        $('.seek-end:not(.disabled)').click(function(){
            Paginate(parseInt($('#total-pages').html()));
        });

        $('#element-count').change(function(){
            countPages();
            Paginate(1);
        });

        $('#current-page').keydown(function(e){
            if(e.keyCode==13)
            {
                Paginate(parseInt($(this).val()));
            }
        })

       $('#submit_button').click(function(){
            var Data='{';
            var Total=$('.data').length;
            var Counter=0;
            $('.data').each(function(){
                Counter++;
                info=$(this).val();
                key=$('.key[index="'+$(this).attr('index')+'"]').val();
                info=info.replace(/"/g, '&#34');
                info=info.replace(/'/g, '&#39');
                Data+=('"'+key+'":"'+info+'"');
                if(Total>Counter)
                    Data+=",";
            });
            Data+="}";
            $('#data_language').val(Data);
            $('#submit').click();
       })

        Paginate(1);
        countPages();
    })
</script>

<div style="width:700;padding-top:5px; float:left;">
    <h2><?php echo lang('editing_language'); ?></h2>
</div>

<form action="language.php?<?php echo $qstr; ?>" method="POST" name="lang" id="lang">
<?php csrf_token(); ?>

 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="language" value="<?php echo $language; ?>">
 <textarea name="data_language" style="display:none" id="data_language"></textarea>

 <table class="list" id="content_table" border="0" cellspacing="1" cellpadding="0" width="940">
    <caption></caption>
    <thead>
        <tr class="fixed">       
            <th width="300px"><a><?php echo lang('key'); ?></a></th>
            <th width="635px"><a><?php echo lang('desc_key'); ?></a></th>
        </tr>
    </thead>
    <tbody>
        <?php $counter=0; ?>    
        <?php foreach (editLanguage($language) as $key => $value): ?>
            <tr id="<?php echo $row['email_id']; ?>">
                <td ><?php echo $key; ?></td>
                <td >
                    <input type="text" name="languageData" index="<?php echo $counter; ?>" class="data" style="width:99.7%" value='<?php echo $value; ?>' />
                    <input type="hidden" name="index" index="<?php echo $counter; ?>" class="key" value="<?php echo $key; ?>">
                </td>
            </tr>
            <?php $counter++; ?> 
        <?php endforeach; ?>
    </tbody>
    <tfoot>
     <tr class="fixed" >
        <td colspan="2" style="width:935px;">
            <div id="pg-wraper">
                <div id="pg-container">
                    <div><span class="icon-pg seek-first"></span></div>
                     <div><span class="icon-pg seek-prev"></span></div>
                     <div><span class="ui-separator"></span></div>
                     <div><span class="description"><?php echo lang('page'); ?></span></div>
                     <div><input id="current-page" class="pg-input" type="text" size="2" maxlength="7" value="0" ></div>
                     <div><span class="description">&nbsp<?php echo lang('of'); ?>&nbsp</span></div>
                     <div><span id="total-pages" class="description"></span></div>
                     <div><span class="ui-separator"></span></div>
                     <div><span class="icon-pg seek-next"></span></div>
                     <div><span class="icon-pg seek-end"></span></div>
                    <div>
                        <select id="element-count" class="pg-selbox">
                            <option role="option" value="25" selected="selected">25</option>
                            <option role="option" value="35">35</option>
                            <option role="option" value="45">45</option>
                            <option role="option" value="55">55</option>
                            <option role="option" value="65">65</option>
                        </select>
                    </div>
                </div>
            </div>

        </td>
     </tr>
    </tfoot>
</table>
<p >
    <input type="submit" id="submit" style="display:none" name="submit" value="<?php echo lang('save_changes'); ?>">
    <input type="button" id="submit_button" name="submit" value="<?php echo lang('save_changes'); ?>">
    <input type="button" name="cancel" value="<?php echo lang('cancel_edit'); ?>" onclick='window.location.href="language.php"'>
</p>

</form>


