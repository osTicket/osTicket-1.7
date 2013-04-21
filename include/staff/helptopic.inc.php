<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');
$info=array();
$qstr='';
if($topic && $_REQUEST['a']!='add') {
    $title=__('Update Help Topic');
    $action='update';
    $submit_text=__('Save Changes');
    $info=$topic->getInfo();
    $info['id']=$topic->getId();
    $info['pid']=$topic->getPid();
    $qstr.='&id='.$topic->getId();
} else {
    $title=__('Add New Help Topic');
    $action='create';
    $submit_text=__('Add Topic');
    $info['isactive']=isset($info['isactive'])?$info['isactive']:1;
    $info['ispublic']=isset($info['ispublic'])?$info['ispublic']:1;
    $qstr.='&a='.$_REQUEST['a'];
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<form action="helptopics.php?<?php echo $qstr; ?>" method="post" id="save">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <h2><?php echo __('Help Topic');?></h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
                <em><?php echo __('Help Topic Information');?></em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required">
               <?php echo __('Topic:');?>
            </td>
            <td>
                <input type="text" size="30" name="topic" value="<?php echo $info['topic']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['topic']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo __('Status:');?>
            </td>
            <td>
                <input type="radio" name="isactive" value="1" <?php echo $info['isactive']?'checked="checked"':''; ?>><?php echo __('Active');?>
                <input type="radio" name="isactive" value="0" <?php echo !$info['isactive']?'checked="checked"':''; ?>><?php echo __('Disabled');?>
                &nbsp;<span class="error">*&nbsp;</span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo __('Type:');?>
            </td>
            <td>
                <input type="radio" name="ispublic" value="1" <?php echo $info['ispublic']?'checked="checked"':''; ?>><?php echo __('Public');?>
                <input type="radio" name="ispublic" value="0" <?php echo !$info['ispublic']?'checked="checked"':''; ?>><?php echo __('Private/Internal');?>
                &nbsp;<span class="error">*&nbsp;</span>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('Parent Topic:');?>
            </td>
            <td>
                <select name="pid">
                    <option value="">&mdash; <?php echo __('Select Parent Topic');?> &mdash;</option>
                    <?php
                    $sql='SELECT topic_id, topic FROM '.TOPIC_TABLE
                        .' WHERE topic_pid=0 '
                        .' ORDER by topic';
                    if(($res=db_query($sql)) && db_num_rows($res)) {
                        while(list($id, $name)=db_fetch_row($res)) {
                            echo sprintf('<option value="%d" %s>%s</option>',
                                    $id, (($info['pid'] && $id==$info['pid'])?'selected="selected"':'') ,$name);
                        }
                    }
                    ?>
                </select> (<em><?php echo __('optional');?></em>)
                &nbsp;<span class="error">&nbsp;<?php echo $errors['pid']; ?></span>
            </td>
        </tr>

        <tr><th colspan="2"><em><?php echo __('New ticket options');?></em></th></tr>
        <tr>
            <td width="180" class="required">
                <?php echo __('Priority:');?>
            </td>
            <td>
                <select name="priority_id">
                    <option value="">&mdash; <?php echo __('Select Priority');?> &mdash;</option>
                    <?php
                    $sql='SELECT priority_id,priority_desc FROM '.PRIORITY_TABLE.' pri ORDER by priority_urgency DESC';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$name)=db_fetch_row($res)){
                            $selected=($info['priority_id'] && $id==$info['priority_id'])?'selected="selected"':'';
                            echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['priority_id']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo __('Department:');?>
            </td>
            <td>
                <select name="dept_id">
                    <option value="">&mdash; <?php echo __('Select Department');?> &mdash;</option>
                    <?php
                    $sql='SELECT dept_id,dept_name FROM '.DEPT_TABLE.' dept ORDER by dept_name';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$name)=db_fetch_row($res)){
                            $selected=($info['dept_id'] && $id==$info['dept_id'])?'selected="selected"':'';
                            echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['dept_id']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('SLA Plan:');?>
            </td>
            <td>
                <select name="sla_id">
                    <option value="0">&mdash; <?php echo __("Department's Default");?> &mdash;</option>
                    <?php
                    if($slas=SLA::getSLAs()) {
                        foreach($slas as $id =>$name) {
                            echo sprintf('<option value="%d" %s>%s</option>',
                                    $id, ($info['sla_id']==$id)?'selected="selected"':'',$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">&nbsp;<?php echo $errors['sla_id']; ?></span>
                <em>(<?php echo __("Overwrites department's SLA");?>)</em>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('Auto-assign To:');?>
            </td>
            <td>
                <select name="assign">
                    <option value="0">&mdash; <?php echo __('Unassigned');?> &mdash;</option>
                                

                    <?php
                    
                                
                    $sql=' SELECT staff_id,CONCAT_WS(", ",lastname,firstname) as name '.
                         ' FROM '.STAFF_TABLE.' WHERE isactive=1 ORDER BY name';
                                
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        echo '<OPTGROUP label="'.__('Staff Members').'">';
                        while (list($id,$name) = db_fetch_row($res)){
                            $k="s$id";
                            $selected = ($info['assign']==$k || $info['staff_id']==$id)?'selected="selected"':'';
                            ?>
                            <option value="<?php echo $k; ?>"<?php echo $selected; ?>><?php echo $name; ?></option>
                            
                        <?php }
                        echo '</OPTGROUP>';
                        
                    }
                    $sql='SELECT team_id, name FROM '.TEAM_TABLE.' WHERE isenabled=1';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        echo '<OPTGROUP label="'.__('Teams').'">';
                        while (list($id,$name) = db_fetch_row($res)){
                            $k="t$id";
                            $selected = ($info['assign']==$k || $info['team_id']==$id)?'selected="selected"':'';
                            ?>
                            <option value="<?php echo $k; ?>"<?php echo $selected; ?>><?php echo $name; ?></option>
                        <?php
                        }
                        echo '</OPTGROUP>';
                    }
                    ?>
                </select>
                &nbsp;<span class="error">&nbsp;<?php echo $errors['assign']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('Ticket auto-response:');?>
            </td>
            <td>
                <input type="checkbox" name="noautoresp" value="1" <?php echo $info['noautoresp']?'checked="checked"':''; ?> >
                    <?php echo __('<strong>Disable</strong> new ticket auto-response for this topic (Overwrites Dept. settings).');?>
            </td>
        </tr>

        <tr>
            <th colspan="2">
                <em><strong><?php echo __('Admin Notes');?></strong>: <?php echo __('Internal notes about the help topic.');?>&nbsp;</em>
            </th>
        </tr>
        <tr>
            <td colspan=2>
                <textarea name="notes" cols="21" rows="8" style="width: 80%;"><?php echo $info['notes']; ?></textarea>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:225px;">
    <input type="submit" name="submit" value="<?php echo $submit_text; ?>">
    <input type="reset"  name="reset"  value="<?php echo __('Reset');?>">
    <input type="button" name="cancel" value="<?php echo __('Cancel');?>" onclick='window.location.href="helptopics.php"'>
</p>
</form>
