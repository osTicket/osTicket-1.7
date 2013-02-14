<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die(_('Access Denied'));

$matches=Filter::getSupportedMatches();
$match_types=Filter::getSupportedMatchTypes();

$info=array();
$qstr='';
if($filter && $_REQUEST['a']!='add'){
    $title=_('Update Filter');
    $action='update';
    $submit_text=_('Save Changes');
    $info=array_merge($filter->getInfo(),$filter->getFlatRules());
    $info['id']=$filter->getId();
    $qstr.='&id='.$filter->getId();
}else {
    $title=_('Add New Filter');
    $action='add';
    $submit_text=_('Add Filter');
    $info['isactive']=isset($info['isactive'])?$info['isactive']:0;
    $qstr.='&a='.urlencode($_REQUEST['a']);
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<form action="filters.php?<?php echo $qstr; ?>" method="post" id="save">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <h2><?=_("Incoming Email Filter")?></h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
                <em><?= _('Filters are executed based on execution order. Filter can target specific ticket source.')?></em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required">
              <?= _('Filter Name')?>:
            </td>
            <td>
                <input type="text" size="30" name="name" value="<?php echo $info['name']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['name']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
              <?= _('Execution Order')?>:
            </td>
            <td>
                <input type="text" size="6" name="execorder" value="<?php echo $info['execorder']; ?>">
                <em>(1...99 )</em>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['execorder']; ?></span>
                &nbsp;&nbsp;&nbsp;
                <input type="checkbox" name="stop_onmatch" value="1" <?php echo $info['stop_onmatch']?'checked="checked"':''; ?> >
                <strong><?= _('Stop')?></strong> <?= _('processing further on match!')?>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?= _('Filter Status')?>:
            </td>
            <td>
                <input type="radio" name="isactive" value="1" <?php echo $info['isactive']?'checked="checked"':''; ?>><strong><?= _('Active')?></strong>
                <input type="radio" name="isactive" value="0" <?php echo !$info['isactive']?'checked="checked"':''; ?>><?= _('Disabled')?>
                &nbsp;<span class="error">*&nbsp;</span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?= _('Target')?>:
            </td>
            <td>
                <select name="target">
                   <option value="">&mdash; <?= _('Select a Target')?> &mdash;</option>
                   <?php
                   foreach(Filter::getTargets() as $k => $v) {
                       echo sprintf('<option value="%s" %s>%s</option>',
                               $k, (($k==$info['target'])?'selected="selected"':''), _($v));
                    }
                    $sql='SELECT email_id,email,name FROM '.EMAIL_TABLE.' email ORDER by name';
                    if(($res=db_query($sql)) && db_num_rows($res)) {
                        echo '<OPTGROUP label="'._('Specific System Email').'">';
                        while(list($id,$email,$name)=db_fetch_row($res)) {
                            $selected=($info['email_id'] && $id==$info['email_id'])?'selected="selected"':'';
                            if($name)
                                $email=Format::htmlchars("$name <$email>");
                            echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$email);
                        }
                        echo '</OPTGROUP>';
                    }
                    ?>
                </select>
                &nbsp;
                <span class="error">*&nbsp;<?php echo $errors['target']; ?></span>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?= _('Filter Rules')?></strong>: <?= _('Rules are applied based on the criteria.')?> &nbsp;<span class="error">*&nbsp;<?php echo $errors['rules']; ?></span></em>
            </th>
        </tr>
        <tr>
            <td colspan=2>
               <em><?= _('Rules Matching Criteria')?>:</em>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="radio" name="match_all_rules" value="1" <?php echo $info['match_all_rules']?'checked="checked"':''; ?>><?= _('Match All')?>
                &nbsp;&nbsp;&nbsp;
                <input type="radio" name="match_all_rules" value="0" <?php echo !$info['match_all_rules']?'checked="checked"':''; ?>><?= _('Match Any')?>
                &nbsp;<span class="error">*&nbsp;</span>
                <em><?= _('(case-insensitive comparison)')?></em>
                
            </td>
        </tr>
        <?php
        $n=($filter?$filter->getNumRules():0)+2; //2 extra rules of unlimited.
        for($i=1; $i<=$n; $i++){ ?>
        <tr id="r<?php echo $i; ?>">
            <td colspan="2">
                <div  style="width:700; float:left;">
                    <select name="rule_w<?php echo $i; ?>">
                        <option value="">&mdash; <?= _('Select One')?> &mdash;</option>
                        <?php
                        foreach($matches as $k=>$v){
                            $sel=($info["rule_w$i"]==$k)?'selected="selected"':'';
                            echo sprintf('<option value="%s" %s>%s</option>',$k,$sel,_($v));
                        }
                        ?>
                    </select>
                    <select name="rule_h<?php echo $i; ?>">
                        <option value="0">&mdash; <?= _('Select One')?> &mdash;</option>
                        <?php
                        foreach($match_types as $k=>$v){
                            $sel=($info["rule_h$i"]==$k)?'selected="selected"':'';
                            echo sprintf('<option value="%s" %s>%s</option>',$k,$sel,_($v));
                        }
                        ?>
                    </select>
                    <input type="text" size="30" name="rule_v<?php echo $i; ?>" value="<?php echo $info["rule_v$i"]; ?>">
                    &nbsp;<span class="error">&nbsp;<?php echo $errors["rule_$i"]; ?></span>
                </div>
                <?php 
                if($info["rule_w$i"] || $info["rule_h$i"] || $info["rule_v$i"]){ ?>
                <div style="float:right;text-align:right;padding-right:20px;"><a href="#" class="clearrule"><?=_("(clear)")?></a></div>
                <?php
                } ?>
                <div class="clear"></div>
            </td>
        </tr>
        <?php
            if($i>=25) //Hardcoded limit of 25 rules...also see class.filter.php
               break;
        } ?>
        <tr>
            <th colspan="2">
                <em><strong><?= _('Filter Actions')?></strong>: <?= _('Can be overwriten by other filters depending on processing order.')?>&nbsp;</em>
            </th>
        </tr>
        <tr>
            <td width="180">
               <?= _('Reject Ticket')?>:
            </td>
            <td>
                <input type="checkbox" name="reject_ticket" value="1" <?php echo $info['reject_ticket']?'checked="checked"':''; ?> >
                    <strong><font class="error"><?= _('Reject Ticket')?></font></strong> <em><?= _('(All other actions and filters are ignored)')?></em>
            </td>
        </tr>
        <tr>
            <td width="180">
               <?= _('Reply-To Email')?>:
            </td>
            <td>
                <input type="checkbox" name="use_replyto_email" value="1" <?php echo $info['use_replyto_email']?'checked="checked"':''; ?> >
                    <strong><?= _('Use')?> </strong><?= _('Reply-To Email')?><em> <?= _('(if available)')?></em>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?= _('Ticket auto-response')?>:
            </td>
            <td>
                <input type="checkbox" name="disable_autoresponder" value="1" <?php echo $info['disable_autoresponder']?'checked="checked"':''; ?> >
                    <strong><?= _('Disable')?></strong> <?= _('auto-response.')?> <em><?= _('(Overwrites Dept. settings)')?></em>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?= _('Canned Response')?>:
            </td>
                <td>
                <select name="canned_response_id">
                    <option value="">&mdash; <?= _('None')?> &mdash;</option>
                    <?php
                    $sql='SELECT canned_id,title FROM '.CANNED_TABLE
                        .' WHERE isenabled ORDER by title';
                    if ($res=db_query($sql)) {
                        while (list($id,$title)=db_fetch_row($res)) {
                            $selected=($info['canned_response_id'] &&
                                    $id==$info['canned_response_id'])
                                ? 'selected="selected"' : '';
                            echo sprintf('<option value="%d" %s>%s</option>',
                                $id, $selected, $title);
                        }
                    }
                    ?>
                </select>
                <em><?= _('(Automatically respond with this canned response)')?></em>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?= _('Department')?>:
            </td>
            <td>
                <select name="dept_id">
                    <option value="">&mdash; <?= _('Default')?> &mdash;</option>
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
                <?= _('Priority')?>:
            </td>
            <td>
                <select name="priority_id">
                    <option value="">&mdash; <?= _('Default')?> &mdash;</option>
                    <?php
                    $sql='SELECT priority_id,priority_desc FROM '.PRIORITY_TABLE.' pri ORDER by priority_urgency DESC';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$name)=db_fetch_row($res)){
                            $selected=($info['priority_id'] && $id==$info['priority_id'])?'selected="selected"':'';
                            echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,_($name));
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['priority_id']; ?></span>
                <em><?= _('(Overwrites department\'s priority)')?></em>
            </td>
        </tr>
        <tr>
            <td width="180">
               <?= _('SLA Plan')?>:
            </td>
            <td>
                <select name="sla_id">
                    <option value="0">&mdash; <?= _('Default')?> &mdash;</option>
                    <?php
                    $sql='SELECT id,name FROM '.SLA_TABLE.' sla ORDER by name';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$name)=db_fetch_row($res)){
                            $selected=($info['sla_id'] && $id==$info['sla_id'])?'selected="selected"':'';
                            echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">&nbsp;<?php echo $errors['sla_id']; ?></span>
                <em><?= _('(Overwrites department\'s SLA)')?></em>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?= _('Auto-assign To')?>:
            </td>
            <td>
                <select name="assign">
                    <option value="0">&mdash; <?= _('Unassigned')?> &mdash;</option>


                    <?php


                    $sql=' SELECT staff_id,CONCAT_WS(", ",lastname,firstname) as name '.
                         ' FROM '.STAFF_TABLE.' WHERE isactive=1 ORDER BY name';

                    if(($res=db_query($sql)) && db_num_rows($res)){
                        echo '<OPTGROUP label="'._('Staff Members').'">';
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
                        echo '<OPTGROUP label="Teams">';
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
            <th colspan="2">
                <em><strong><?= _('Admin Notes')?></strong>: <?= _('Internal notes')?>.&nbsp;</em>
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
    <input type="reset"  name="reset"  value="<?= _('Reset')?>">
    <input type="button" name="cancel" value="<?= _('Cancel')?>" onclick='window.location.href="filters.php"'>
</p>
</form>
