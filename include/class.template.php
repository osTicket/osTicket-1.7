<?php
/*********************************************************************
    class.template.php

    Email Template

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require_once INCLUDE_DIR.'class.yaml.php';
require_once(INCLUDE_DIR.'languages/language_control/languages_processor.php');

class EmailTemplateGroup {

    var $id;
    var $ht;
    var $_templates;

    function all_names()
    {
        return $all_names=array(
        'ticket.autoresp'=>array(
            'name'=>lang('tic_auto_response'),
            'desc'=>lang('auto_resp_sent')),
        'ticket.autoreply'=>array(
            'name'=>lang('new_ticket_reply'),
            'desc'=>lang('cant_auto_sent')),
        'message.autoresp'=>array(
            'name'=>lang('mess_auto_resp'),
            'desc'=>lang('confirm_sent')),
        'ticket.notice'=>array(
            'name'=>lang('new_ticket_notice'),
            'desc'=>lang('notice_sent_user')),
        'ticket.overlimit'=>array(
            'name'=>lang('over_limit_not'),
            'desc'=>lang('one_time_notice')),
        'ticket.reply'=>array(
            'name'=>lang('response_temp'),
            'desc'=>lang('temp_in_ticket_r')),
        'ticket.alert'=>array(
            'name'=>lang('new_ticket_alert'),
            'desc'=>lang('alert_sent_staff')),
        'message.alert'=>array(
            'name'=>lang('new_message_alert'),
            'desc'=>lang('alert_staff_enab')),
        'note.alert'=>array(
            'name'=>lang('intern_note_alert'),
            'desc'=>lang('alert_sl_staff')),
        'assigned.alert'=>array(
            'name'=>lang('ticket_assig_alert'),
            'desc'=>lang('alert_to_staff')),
        'transfer.alert'=>array(
            'name'=>lang('ticket_transfer'),
            'desc'=>lang('alert_ticket_trans')),
        'ticket.overdue'=>array(
            'name'=>lang('overdue_ticket'),
            'desc'=>lang('alert_on_overdue')),
        'staff.pwreset' => array(
            'name' => lang('staff_reset_pass'),
            'desc' => lang('pass_reset_link'),
            'default' => 'templates/staff.pwreset.txt'),
        );
    }

    function EmailTemplateGroup($id){
        $this->id=0;
        $this->load($id);
    }

    function load($id) {

        if(!$id && !($id=$this->getId()))
            return false;

        $sql='SELECT tpl.*,count(dept.tpl_id) as depts '
            .' FROM '.EMAIL_TEMPLATE_GRP_TABLE.' tpl '
            .' LEFT JOIN '.DEPT_TABLE.' dept USING(tpl_id) '
            .' WHERE tpl.tpl_id='.db_input($id)
            .' GROUP BY tpl.tpl_id';

        if(!($res=db_query($sql))|| !db_num_rows($res))
            return false;


        $this->ht=db_fetch_array($res);
        $this->id=$this->ht['tpl_id'];

        return true;
    }

    function reload() {
        return $this->load($this->getId());
    }

    function getId(){
        return $this->id;
    }

    function getName(){
        return $this->ht['name'];
    }

    function getNotes(){
        return $this->ht['notes'];
    }

    function isEnabled() {
         return ($this->ht['isactive']);
    }

    function isActive(){
        return $this->isEnabled();
    }

    function getLanguage() {
        return 'en_US';
    }

    function isInUse(){
        global $cfg;

        return ($this->ht['depts'] || ($cfg && $this->getId()==$cfg->getDefaultTemplateId()));
    }

    function getHashtable() {
        return $this->ht;
    }

    function getInfo() {
        return $this->getHashtable();
    }

    function setStatus($status){

        $sql='UPDATE '.EMAIL_TEMPLATE_GRP_TABLE.' SET updated=NOW(), isactive='.db_input($status?1:0)
            .' WHERE tpl_id='.db_input($this->getId());

        return (db_query($sql) && db_affected_rows());
    }

    function getTemplateDescription($name) {
        $names = $this->all_names();
        return $names[$name];
    }

    function getMsgTemplate($name) {
        global $ost;

        if ($tpl=EmailTemplate::lookupByName($this->getId(), $name, $this))
            return $tpl;

        if ($tpl=EmailTemplate::fromInitialData($name, $this))
            return $tpl;

        $ost->logWarning('Template Fetch Error', "Unable to fetch '$name' template - id #".$this->getId());
        return false;
    }

    function getTemplates() {
        $lang = getDefaultLanguage(true);

        if (!$this->_tempates) {
            $this->_templates = array();
            $sql = 'SELECT id, code_name FROM '.EMAIL_TEMPLATE_TABLE
                .' WHERE tpl_id='.db_input($this->getId())
                .' AND lang="'.$lang.'" '
                .' ORDER BY code_name';
            $res = db_query($sql);
            while (list($id, $cn)=db_fetch_row($res))
                $this->_templates[$cn] = EmailTemplate::lookup($id, $this);
        }
        return $this->_templates;
    }

    function getUndefinedTemplateNames() {
        $list = $this->all_names();
        foreach ($this->getTemplates() as $cn=>$tpl)
            unset($list[$cn]);
        return $list;
    }


    function getNewTicketAlertMsgTemplate() {
        return $this->getMsgTemplate('ticket.alert');
    }

    function getNewMessageAlertMsgTemplate() {
        return $this->getMsgTemplate('message.alert');
    }

    function getNewTicketNoticeMsgTemplate() {
        return $this->getMsgTemplate('ticket.notice');
    }

    function getNewMessageAutorepMsgTemplate() {
        return $this->getMsgTemplate('message.autoresp');
    }

    function getAutoRespMsgTemplate() {
        return $this->getMsgTemplate('ticket.autoresp');
    }

    function getAutoReplyMsgTemplate() {
        return $this->getMsgTemplate('ticket.autoreply');
    }

    function getReplyMsgTemplate() {
        return $this->getMsgTemplate('ticket.reply');
    }

    function getOverlimitMsgTemplate() {
        return $this->getMsgTemplate('ticket.overlimit');
    }

    function getNoteAlertMsgTemplate() {
        return $this->getMsgTemplate('note.alert');
    }

    function getTransferAlertMsgTemplate() {
        return $this->getMsgTemplate('transfer.alert');
    }

    function getAssignedAlertMsgTemplate() {
        return $this->getMsgTemplate('assigned.alert');
    }

    function getOverdueAlertMsgTemplate() {
        return $this->getMsgTemplate('ticket.overdue');
    }
    
    function update($vars,&$errors) {

        if(!$vars['isactive'] && $this->isInUse())
            $errors['isactive']=lang('template_cant_disabled');

        if(!$this->save($this->getId(),$vars,$errors))
            return false;

        $this->reload();

        return true;
    }

    function enable(){
        return ($this->setStatus(1));
    }

    function disable(){
        return (!$this->isInUse() && $this->setStatus(0));
    }

    function delete(){
        global $cfg;

        if($this->isInUse() || $cfg->getDefaultTemplateId()==$this->getId())
            return 0;

        $sql='DELETE FROM '.EMAIL_TEMPLATE_GRP_TABLE
            .' WHERE tpl_id='.db_input($this->getId()).' LIMIT 1';
        if(db_query($sql) && ($num=db_affected_rows())) {
            //isInuse check is enough - but it doesn't hurt make sure deleted tpl is not in-use.
            db_query('UPDATE '.DEPT_TABLE.' SET tpl_id=0 WHERE tpl_id='.db_input($this->getId()));
            db_query('DELETE FROM '.EMAIL_TEMPLATE_TABLE
                .' WHERE tpl_id='.db_input($this->getId()));
        }

        return $num;
    }

    function create($vars,&$errors) {
        return EmailTemplateGroup::save(0,$vars,$errors);
    }

    function add($vars, &$errors) {
        return self::lookup(self::create($vars, $errors));
    }

    function getIdByName($name){
        $sql='SELECT tpl_id FROM '.EMAIL_TEMPLATE_GRP_TABLE.' WHERE name='.db_input($name);
        if(($res=db_query($sql)) && db_num_rows($res))
            list($id)=db_fetch_row($res);

        return $id;
    }

    function lookup($id){
        return ($id && is_numeric($id) && ($t= new EmailTemplateGroup($id)) && $t->getId()==$id)?$t:null;
    }

    function save($id, $vars, &$errors) {
        global $ost;

        $tpl=null;
        $vars['name']=Format::striptags(trim($vars['name']));

        if($id && $id!=$vars['tpl_id'])
            $errors['err']=lang('internal_error_try');

        if(!$vars['name'])
            $errors['name']=lang('name_required');
        elseif(($tid=EmailTemplateGroup::getIdByName($vars['name'])) && $tid!=$id)
            $errors['name']=lang('temp_exist');

        if(!$id && (!$vars['tpl_id'] || !($tpl=EmailTemplateGroup::lookup($vars['tpl_id']))))
            $errors['tpl_id']=lang('selection_requir');

        if($errors) return false;

        $sql=' updated=NOW() '
            .' ,name='.db_input($vars['name'])
            .' ,isactive='.db_input($vars['isactive'])
            .' ,notes='.db_input($vars['notes']);

        if($id) {
            $sql='UPDATE '.EMAIL_TEMPLATE_GRP_TABLE.' SET '.$sql.' WHERE tpl_id='.db_input($id);
            if(db_query($sql))
                return true;

            $errors['err']=lang('inter_error_temp');

        } elseif($tpl && ($info=$tpl->getInfo())) {

            $sql='INSERT INTO '.EMAIL_TEMPLATE_GRP_TABLE
                .' SET created=NOW(), '.$sql;
            if(!db_query($sql) || !($new_id=db_insert_id())) {
                $errors['err']=lang('unable_cr_temp');
                return false;
            }

            $sql='INSERT INTO '.EMAIL_TEMPLATE_TABLE.'
                    (created, updated, tpl_id, code_name, subject, body)
                    SELECT NOW() as created, NOW() as updated, '.db_input($new_id)
                    .' as tpl_id, code_name, subject, body
                    FROM '.EMAIL_TEMPLATE_TABLE
                    .' WHERE tpl_id='.db_input($tpl->getId());

            if(db_query($sql) && db_insert_id())
                return $new_id;
        }

        return false;
    }
}

class EmailTemplate {

    var $id;
    var $ht;
    var $_group;

    function EmailTemplate($id, $group=null){
        $this->id=0;
        if ($id) $this->load($id);
        if ($group) $this->_group = $group;
    }

    function load($id) {

        if(!$id && !($id=$this->getId()))
            return false;

        $sql='SELECT * FROM '.EMAIL_TEMPLATE_TABLE
            .' WHERE id='.db_input($id);

        if(!($res=db_query($sql))|| !db_num_rows($res))
            return false;


        $this->ht=db_fetch_array($res);
        $this->id=$this->ht['id'];

        return true;
    }

    function reload() {
        return $this->load($this->getId());
    }

    function getId(){
        return $this->id;
    }

    function asArray() {
        return array(
            'id' => $this->getId(),
            'subj' => $this->getSubject(),
            'body' => $this->getBody(),
        );
    }

    function getSubject() {
        return $this->ht['subject'];
    }

    function getBody() {
        return $this->ht['body'];
    }

    function getCodeName() {
        return $this->ht['code_name'];
    }

    function getTplId() {
        return $this->ht['tpl_id'];
    }

    function getGroup() {
        if (!isset($this->_group))
            $this->_group = EmailTemplateGroup::lookup($this->getTplId());
        return $this->_group;

    }

    function getDescription() {
        return $this->getGroup()->getTemplateDescription($this->ht['code_name']);
    }

    function update($vars, &$errors) {

        if(!$this->save($this->getId(),$vars,$errors))
            return false;

        $this->reload();

        return true;
    }

    function save($id, $vars, &$errors) {
        if(!$vars['subj'])
            $errors['subj']=lang('message_subject_req');

        if(!$vars['body'])
            $errors['body']=lang('message_body_req');

        if (!$id) {
            if (!$vars['tpl_id'])
                $errors['tpl_id']=lang('template_cant_disabled');
            if (!$vars['code_name'])
                $errprs['code_name']=lang('code_name_required');
        }

        if ($errors)
            return false;

        if ($id) {
            $sql='UPDATE '.EMAIL_TEMPLATE_TABLE.' SET updated=NOW() '
                .', subject='.db_input($vars['subj'])
                .', body='.db_input($vars['body'])
                .' WHERE id='.db_input($this->getId());

            return (db_query($sql));
        } else {
            $sql='INSERT INTO '.EMAIL_TEMPLATE_TABLE.' SET created=NOW(),
                updated=NOW(), tpl_id='.db_input($vars['tpl_id'])
                .', code_name='.db_input($vars['code_name'])
                .', subject='.db_input($vars['subj'])
                .', body='.db_input($vars['body']);
            if (db_query($sql) && ($id=db_insert_id()))
                return $id;
        }
        return null;
    }

    function create($vars, &$errors) {
        return self::save(0, $vars, $errors);
    }

    function add($vars, &$errors) {
        return self::lookup(self::create($vars, $errors));
    }

    function lookupByName($tpl_id, $name, $group=null) {
        $lang = getDefaultLanguage(true);
        $sql = 'SELECT id FROM '.EMAIL_TEMPLATE_TABLE
            .' WHERE tpl_id='.db_input($tpl_id)
            .' AND lang="'.$lang.'" '
            .' AND code_name='.db_input($name);
        if (($res=db_query($sql)) && ($id=db_result($res)))
            return self::lookup($id, $group);

        return false;
    }

    function lookup($id, $group=null) {
        return ($id && is_numeric($id) && ($t= new EmailTemplate($id, $group)) && $t->getId()==$id)?$t:null;
    }

    /**
     * Load the template from the initial_data directory. The format of the
     * file should be free flow text. The first line is the subject and the
     * rest of the file is the body.
     */
    function fromInitialData($name, $group=null) {
        $templ = new EmailTemplate(0, $group);
        $lang = ($group) ? $group->getLanguage() : 'en_US';
        $info = YamlDataParser::load(I18N_DIR . "$lang/templates/$name.yaml");
        if (isset($info['subject']) && isset($info['body'])) {
            $templ->ht = $info;
            return $templ;
        }
        raise_error("$lang/templates/$name.yaml: "
            . lang('temp_def_sub_body'),
            'InitialDataError');
        return false;
    }
}
?>
