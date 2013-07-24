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

class EmailTemplateGroup {

    var $id;
    var $ht;
    var $_templates;
    var $all_names=array(
        'ticket.autoresp'=>array(
            'name'=>'New Ticket Auto-response',
            'desc'=>'Autoresponse sent to user, if enabled, on new ticket.'),
        'ticket.autoreply'=>array(
            'name'=>'New Ticket Auto-reply',
            'desc'=>'Canned Auto-reply sent to user on new ticket, based on filter matches. Overwrites "normal" auto-response.'),
        'message.autoresp'=>array(
            'name'=>'New Message Auto-response',
            'desc'=>'Confirmation sent to user when a new message is appended to an existing ticket.'),
        'ticket.notice'=>array(
            'name'=>'New Ticket Notice',
            'desc'=>'Notice sent to user, if enabled, on new ticket created by staff on their behalf (e.g phone calls).'),
        'ticket.overlimit'=>array(
            'name'=>'Over Limit Notice',
            'desc'=>'A one-time notice sent, if enabled, when user has reached the maximum allowed open tickets.'),
        'ticket.reply'=>array(
            'name'=>'Response/Reply Template',
            'desc'=>'Template used on ticket response/reply'),
        'ticket.alert'=>array(
            'name'=>'New Ticket Alert',
            'desc'=>'Alert sent to staff, if enabled, on new ticket.'),
        'message.alert'=>array(
            'name'=>'New Message Alert',
            'desc'=>'Alert sent to staff, if enabled, when user replies to an existing ticket.'),
        'note.alert'=>array(
            'name'=>'Internal Note Alert',
            'desc'=>'Alert sent to selected staff, if enabled, on new internal note.'),
        'assigned.alert'=>array(
            'name'=>'Ticket Assignment Alert',
            'desc'=>'Alert sent to staff on ticket assignment.'),
        'transfer.alert'=>array(
            'name'=>'Ticket Transfer Alert',
            'desc'=>'Alert sent to staff on ticket transfer.'),
        'ticket.overdue'=>array(
            'name'=>'Overdue Ticket Alert',
            'desc'=>'Alert sent to staff on stale or overdue tickets.'),
        'staff.pwreset' => array(
            'name' => 'Staff Password Reset',
            'desc' => 'Notice sent to staff with the password reset link.',
            'default' => 'templates/staff.pwreset.txt'),
        );

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
        return $this->all_names[$name];
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
        if (!$this->_tempates) {
            $this->_templates = array();
            $sql = 'SELECT id, code_name FROM '.EMAIL_TEMPLATE_TABLE
                .' WHERE tpl_id='.db_input($this->getId())
                .' ORDER BY code_name';
            $res = db_query($sql);
            while (list($id, $cn)=db_fetch_row($res))
                $this->_templates[$cn] = EmailTemplate::lookup($id, $this);
        }
        return $this->_templates;
    }

    function getUndefinedTemplateNames() {
        $list = $this->all_names;
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
            $errors['isactive']='Template in use cannot be disabled!';

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
            $errors['err']='Internal error. Try again';

        if(!$vars['name'])
            $errors['name']='Name required';
        elseif(($tid=EmailTemplateGroup::getIdByName($vars['name'])) && $tid!=$id)
            $errors['name']='Template name already exists';

        if(!$id && (!$vars['tpl_id'] || !($tpl=EmailTemplateGroup::lookup($vars['tpl_id']))))
            $errors['tpl_id']='Selection required';

        if($errors) return false;

        $sql=' updated=NOW() '
            .' ,name='.db_input($vars['name'])
            .' ,isactive='.db_input($vars['isactive'])
            .' ,notes='.db_input($vars['notes']);

        if($id) {
            $sql='UPDATE '.EMAIL_TEMPLATE_GRP_TABLE.' SET '.$sql.' WHERE tpl_id='.db_input($id);
            if(db_query($sql))
                return true;

            $errors['err']='Unable to update the template. Internal error occurred';

        } elseif($tpl && ($info=$tpl->getInfo())) {

            $sql='INSERT INTO '.EMAIL_TEMPLATE_GRP_TABLE
                .' SET created=NOW(), '.$sql;
            if(!db_query($sql) || !($new_id=db_insert_id())) {
                $errors['err']='Unable to create template. Internal error';
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
            $errors['subj']='Message subject required';

        if(!$vars['body'])
            $errors['body']='Message body required';

        if (!$id) {
            if (!$vars['tpl_id'])
                $errors['tpl_id']='Template group required';
            if (!$vars['code_name'])
                $errprs['code_name']='Code name required';
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
        $sql = 'SELECT id FROM '.EMAIL_TEMPLATE_TABLE
            .' WHERE tpl_id='.db_input($tpl_id)
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
            . 'Email templates must define both "subject" and "body" parts of the template',
            'InitialDataError');
        return false;
    }
}
?>
