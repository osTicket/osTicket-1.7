<?php
/*********************************************************************
    class.nav.php

    Navigation helper classes. Pointless BUT helps keep navigation clean and free from errors.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require_once(INCLUDE_DIR.'languages/language_control/languages_processor.php');
class StaffNav {
    var $tabs=array();
    var $submenus=array();

    var $activetab;
    var $activemenu;
    var $panel;

    var $staff;

    function StaffNav($staff, $panel='staff'){
        $this->staff=$staff;
        $this->panel=strtolower($panel);
        $this->tabs=$this->getTabs();
        $this->submenus=$this->getSubMenus();
    }

    function getPanel(){
        return $this->panel;
    }

    function isAdminPanel(){
        return (!strcasecmp($this->getPanel(),'admin'));
    }

    function isStaffPanel() {
        return (!$this->isAdminPanel());
    }

    function setTabActive($tab, $menu=''){

        if($this->tabs[$tab]){
            $this->tabs[$tab]['active']=true;
            if($this->activetab && $this->activetab!=$tab && $this->tabs[$this->activetab])
                 $this->tabs[$this->activetab]['active']=false;

            $this->activetab=$tab;
            if($menu) $this->setActiveSubMenu($menu, $tab);

            return true;
        }

        return false;
    }

    function setActiveTab($tab, $menu=''){
        return $this->setTabActive($tab, $menu);
    }

    function getActiveTab(){
        return $this->activetab;
    }

    function setActiveSubMenu($mid, $tab='') {
        if(is_numeric($mid))
            $this->activeMenu = $mid;
        elseif($mid && $tab && ($subNav=$this->getSubNav($tab))) {
            foreach($subNav as $k => $menu) {
                if(strcasecmp($mid, $menu['href'])) continue;

                $this->activeMenu = $k+1;
                break;
            }
        }
    }

    function getActiveMenu() {
        return $this->activeMenu;
    }

    function addSubMenu($item,$active=false){

        $this->submenus[$this->getPanel().'.'.$this->activetab][]=$item;
        if($active)
            $this->activeMenu=sizeof($this->submenus[$this->getPanel().'.'.$this->activetab]);
    }


    function getTabs(){

        if(!$this->tabs) {
            $this->tabs=array();
            $this->tabs['dashboard']=array('desc'=>lang('Dashboard'),'href'=>'dashboard.php','title'=> lang('staff_dashboard'));
            $this->tabs['tickets']=array('desc'=>lang('tickets'),'href'=>'tickets.php','title'=>lang('ticket_queue'));
            $this->tabs['kbase']=array('desc'=>lang('Knowledgebase'),'href'=>'kb.php','title'=>lang('Knowledgebase'));
        }

        return $this->tabs;
    }

    function getSubMenus(){ //Private.

        $staff = $this->staff;
        $submenus=array();
        foreach($this->getTabs() as $k=>$tab){
            $subnav=array();
            switch(strtolower($k)){
                case 'tickets':
                    $subnav[]=array('desc'=>lang('tickets'),'href'=>'tickets.php','iconclass'=>'Ticket', 'droponly'=>true);
                    if($staff) {
                        if(($assigned=$staff->getNumAssignedTickets()))
                            $subnav[]=array('desc'=>lang("ticket_assigned"),
                                            'href'=>'tickets.php?status=assigned',
                                            'iconclass'=>'assignedTickets',
                                            'droponly'=>true);

                        if($staff->canCreateTickets())
                            $subnav[]=array('desc'=>lang('new_ticket'),
                                            'href'=>'tickets.php?a=open',
                                            'iconclass'=>'newTicket',
                                            'droponly'=>true);
                    }
                    break;
                case 'dashboard':
                    $subnav[]=array('desc'=>lang('Dashboard'),'href'=>'dashboard.php','iconclass'=>'logs');
                    $subnav[]=array('desc'=>lang('staff_directory'),'href'=>'directory.php','iconclass'=>'teams');
                    $subnav[]=array('desc'=>lang('my_profile'),'href'=>'profile.php','iconclass'=>'users');
                    break;
                case 'kbase':
                    $subnav[]=array('desc'=>lang('faqs'),'href'=>'kb.php', 'urls'=>array('faq.php'), 'iconclass'=>'kb');
                    if($staff) {
                        if($staff->canManageFAQ())
                            $subnav[]=array('desc'=>lang('categories'),'href'=>'categories.php','iconclass'=>'faq-categories');
                        if($staff->canManageCannedResponses())
                            $subnav[]=array('desc'=>lang('not_responses'),'href'=>'canned.php','iconclass'=>'canned');
                    }
                   break;
            }
            if($subnav)
                $submenus[$this->getPanel().'.'.strtolower($k)]=$subnav;
        }

        return $submenus;
    }

    function getSubMenu($tab=null){
        $tab=$tab?$tab:$this->activetab;
        return $this->submenus[$this->getPanel().'.'.$tab];
    }

    function getSubNav($tab=null){
        return $this->getSubMenu($tab);
    }

}

class AdminNav extends StaffNav{

    function AdminNav($staff){
        parent::StaffNav($staff, 'admin');
    }

    function getTabs(){


        if(!$this->tabs){

            $tabs=array();
            $tabs['dashboard']=array('desc'=>lang('Dashboard'),'href'=>'logs.php','title'=>lang('admin_dashboard'));
            $tabs['settings']=array('desc'=>lang('settings'),'href'=>'settings.php','title'=>lang('system_settings'));
            $tabs['manage']=array('desc'=>lang('manage'),'href'=>'helptopics.php','title'=>lang('manage_options'));
            $tabs['emails']=array('desc'=>lang('emails'),'href'=>'emails.php','title'=>lang('email_settings'));
            $tabs['staff']=array('desc'=>lang('staff'),'href'=>'staff.php','title'=>lang('manage_staff'));
            $tabs['language']=array('desc'=>lang('languages'),'href'=>'language.php','title'=>lang('manage_language'));
            $this->tabs=$tabs;
        }

        return $this->tabs;
    }

    function getSubMenus(){

        $submenus=array();
        foreach($this->getTabs() as $k=>$tab){
            $subnav=array();
            switch(strtolower($k)){
                case 'dashboard':
                    $subnav[]=array('desc'=>lang('system_logs'),'href'=>'logs.php','iconclass'=>'logs');
                    break;
                case 'settings':
                    $subnav[]=array('desc'=>lang('system_and_pref'),'href'=>'settings.php?t=system','iconclass'=>'preferences');
                    $subnav[]=array('desc'=>lang('tickets'),'href'=>'settings.php?t=tickets','iconclass'=>'ticket-settings');
                    $subnav[]=array('desc'=>lang('emails'),'href'=>'settings.php?t=emails','iconclass'=>'email-settings');
                    $subnav[]=array('desc'=>lang('Knowledgebase'),'href'=>'settings.php?t=kb','iconclass'=>'kb-settings');
                    $subnav[]=array('desc'=>lang('autoresponder'),'href'=>'settings.php?t=autoresp','iconclass'=>'email-autoresponders');
                    $subnav[]=array('desc'=>lang('alerts_and_not'),'href'=>'settings.php?t=alerts','iconclass'=>'alert-settings');
                    break;
                case 'manage':
                    $subnav[]=array('desc'=>lang('help_topics'),'href'=>'helptopics.php','iconclass'=>'helpTopics');
                    $subnav[]=array('desc'=>lang('tickets_filters'),'href'=>'filters.php',
                                        'title'=>lang('tickets_filters'),'iconclass'=>'ticketFilters');
                    $subnav[]=array('desc'=>lang('sla_plans'),'href'=>'slas.php','iconclass'=>'sla');
                    $subnav[]=array('desc'=>lang('apy_keys'),'href'=>'apikeys.php','iconclass'=>'api');
                    break;
                case 'emails':
                    $subnav[]=array('desc'=>lang('emails'),'href'=>'emails.php', 'title'=>lang('email_address'), 'iconclass'=>'emailSettings');
                    $subnav[]=array('desc'=>lang('banlist'),'href'=>'banlist.php',
                                        'title'=>lang('banned_emails'),'iconclass'=>'emailDiagnostic');
                    $subnav[]=array('desc'=>lang('templates'),'href'=>'templates.php','title'=>lang('email_temp'),'iconclass'=>'emailTemplates');
                    $subnav[]=array('desc'=>lang('diagnostic'),'href'=>'emailtest.php', 'title'=>lang('email_diagnostic'), 'iconclass'=>'emailDiagnostic');
                    break;
                case 'staff':
                    $subnav[]=array('desc'=>lang('staff_and_member'),'href'=>'staff.php','iconclass'=>'users');
                    $subnav[]=array('desc'=>lang('teams'),'href'=>'teams.php','iconclass'=>'teams');
                    $subnav[]=array('desc'=>lang('groups'),'href'=>'groups.php','iconclass'=>'groups');
                    $subnav[]=array('desc'=>lang('departments'),'href'=>'departments.php','iconclass'=>'departments');
                    break;
                case 'language':
                    $subnav[]=array('desc'=>lang('manage_language'),'href'=>'language.php','iconclass'=>'users');
                    break;

            }
            if($subnav)
                $submenus[$this->getPanel().'.'.strtolower($k)]=$subnav;
        }

        return $submenus;
    }
}

class UserNav {

    var $navs=array();
    var $activenav;

    var $user;

    function UserNav($user=null, $active=''){

        $this->user=$user;
        $this->navs=$this->getNavs();
        if($active)
            $this->setActiveNav($active);
    }

    function setActiveNav($nav){

        if($nav && $this->navs[$nav]){
            $this->navs[$nav]['active']=true;
            if($this->activenav && $this->activenav!=$nav && $this->navs[$this->activenav])
                 $this->navs[$this->activenav]['active']=false;

            $this->activenav=$nav;

            return true;
        }

        return false;
    }

    function getNavLinks(){
        global $cfg;

        //Paths are based on the root dir.
        if(!$this->navs){

            $navs = array();
            $user = $this->user;
            $navs['home']=array('desc'=>lang('support_center'),'href'=>'index.php','title'=>'');
            if($cfg && $cfg->isKnowledgebaseEnabled())
                $navs['kb']=array('desc'=>lang('Knowledgebase'),'href'=>'kb/index.php','title'=>'');

            $navs['new']=array('desc'=>lang('open_new_ticket'),'href'=>'open.php','title'=>'');
            if($user && $user->isValid()) {
                if($cfg && $cfg->showRelatedTickets()) {
                    $navs['tickets']=array('desc'=>sprintf(lang('my_tickets').'(%d)',$user->getNumTickets()),
                                           'href'=>'tickets.php',
                                            'title'=>lang('show_all_tickets'));
                } else {
                    $navs['tickets']=array('desc'=>lang('ticket_thread'),
                                           'href'=>sprintf('tickets.php?id=%d',$user->getTicketID()),
                                           'title'=>lang('tickets_status'));
                }
            } else {
                $navs['status']=array('desc'=>lang('check_ticket_stat'),'href'=>'view.php','title'=>'');
            }
            $this->navs=$navs;
        }

        return $this->navs;
    }

    function getNavs(){
        return $this->getNavLinks();
    }

}

?>
