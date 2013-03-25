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
            $this->tabs['dashboard']=array('desc'=>__('Dashboard'),'href'=>'dashboard.php','title'=>__('Staff Dashboard'));
            $this->tabs['tickets']=array('desc'=>__('Tickets'),'href'=>'tickets.php','title'=>__('Ticket Queue'));
            $this->tabs['kbase']=array('desc'=>__('Knowledgebase'),'href'=>'kb.php','title'=>__('Knowledgebase'));
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
                    $subnav[]=array('desc'=>__('Tickets'),'href'=>'tickets.php','iconclass'=>'Ticket', 'droponly'=>true);
                    if($staff) {
                        if(($assigned=$staff->getNumAssignedTickets()))
                            $subnav[]=array('desc'=>__('My&nbsp;Tickets')." ($assigned)",
                                            'href'=>'tickets.php?status=assigned',
                                            'iconclass'=>'assignedTickets',
                                            'droponly'=>true);

                        if($staff->canCreateTickets())
                            $subnav[]=array('desc'=>__('New&nbsp;Ticket'),
                                            'href'=>'tickets.php?a=open',
                                            'iconclass'=>'newTicket',
                                            'droponly'=>true);
                    }
                    break;
                case 'dashboard':
                    $subnav[]=array('desc'=>__('Dashboard'),'href'=>'dashboard.php','iconclass'=>'logs');
                    $subnav[]=array('desc'=>__('Staff&nbsp;Directory'),'href'=>'directory.php','iconclass'=>'teams');
                    $subnav[]=array('desc'=>__('My&nbsp;Profile'),'href'=>'profile.php','iconclass'=>'users');
                    break;
                case 'kbase':
                    $subnav[]=array('desc'=>__('FAQs'),'href'=>'kb.php', 'urls'=>array('faq.php'), 'iconclass'=>'kb');
                    if($staff) {
                        if($staff->canManageFAQ())
                            $subnav[]=array('desc'=>__('Categories'),'href'=>'categories.php','iconclass'=>'faq-categories');
                        if($staff->canManageCannedResponses())
                            $subnav[]=array('desc'=>__('Canned&nbsp;Responses'),'href'=>'canned.php','iconclass'=>'canned');
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
            $tabs['dashboard']=array('desc'=>__('Dashboard'),'href'=>'logs.php','title'=>__('Admin Dashboard'));
            $tabs['settings']=array('desc'=>__('Settings'),'href'=>'settings.php','title'=>__('System Settings'));
            $tabs['manage']=array('desc'=>__('Manage'),'href'=>'helptopics.php','title'=>__('Manage Options'));
            $tabs['emails']=array('desc'=>__('Emails'),'href'=>'emails.php','title'=>__('Email Settings'));
            $tabs['staff']=array('desc'=>__('Staff'),'href'=>'staff.php','title'=>__('Manage Staff'));
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
                    $subnav[]=array('desc'=>__('System&nbsp;Logs'),'href'=>'logs.php','iconclass'=>'logs');
                    break;
                case 'settings':
                    $subnav[]=array('desc'=>__('System&nbsp;Preferences'),'href'=>'settings.php?t=system','iconclass'=>'preferences');
                    $subnav[]=array('desc'=>__('Tickets'),'href'=>'settings.php?t=tickets','iconclass'=>'ticket-settings');
                    $subnav[]=array('desc'=>__('Emails'),'href'=>'settings.php?t=emails','iconclass'=>'email-settings');
                    $subnav[]=array('desc'=>__('Knowledgebase'),'href'=>'settings.php?t=kb','iconclass'=>'kb-settings');
                    $subnav[]=array('desc'=>__('Autoresponder'),'href'=>'settings.php?t=autoresp','iconclass'=>'email-autoresponders');
                    $subnav[]=array('desc'=>__('Alerts&nbsp;&amp;&nbsp;Notices'),'href'=>'settings.php?t=alerts','iconclass'=>'alert-settings');
                    break;
                case 'manage':
                    $subnav[]=array('desc'=>__('Help&nbsp;Topics'),'href'=>'helptopics.php','iconclass'=>'helpTopics');
                    $subnav[]=array('desc'=>__('Ticket&nbsp;Filters'),'href'=>'filters.php',
                                        'title'=>__('Ticket&nbsp;Filters'),'iconclass'=>'ticketFilters');
                    $subnav[]=array('desc'=>__('SLA&nbsp;Plans'),'href'=>'slas.php','iconclass'=>'sla');
                    $subnav[]=array('desc'=>__('API&nbsp;Keys'),'href'=>'apikeys.php','iconclass'=>'api');
                    break;
                case 'emails':
                    $subnav[]=array('desc'=>__('Emails'),'href'=>'emails.php', 'title'=>__('Email Addresses'), 'iconclass'=>'emailSettings');
                    $subnav[]=array('desc'=>__('Banlist'),'href'=>'banlist.php',
                                        'title'=>__('Banned&nbsp;Emails'),'iconclass'=>'emailDiagnostic');
                    $subnav[]=array('desc'=>__('Templates'),'href'=>'templates.php','title'=>__('Email Templates'),'iconclass'=>'emailTemplates');
                    $subnav[]=array('desc'=>__('Diagnostic'),'href'=>'emailtest.php', 'title'=>__('Email Diagnostic'), 'iconclass'=>'emailDiagnostic');
                    break;
                case 'staff':
                    $subnav[]=array('desc'=>__('Staff&nbsp;Members'),'href'=>'staff.php','iconclass'=>'users');
                    $subnav[]=array('desc'=>__('Teams'),'href'=>'teams.php','iconclass'=>'teams');
                    $subnav[]=array('desc'=>__('Groups'),'href'=>'groups.php','iconclass'=>'groups');
                    $subnav[]=array('desc'=>__('Departments'),'href'=>'departments.php','iconclass'=>'departments');
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
            $navs['home']=array('desc'=>__('Support&nbsp;Center&nbsp;Home'),'href'=>'index.php','title'=>'');
            if($cfg && $cfg->isKnowledgebaseEnabled())
                $navs['kb']=array('desc'=>__('Knowledgebase'),'href'=>'kb/index.php','title'=>'');

            $navs['new']=array('desc'=>__('Open&nbsp;New&nbsp;Ticket'),'href'=>'open.php','title'=>'');
            if($user && $user->isValid()) {
                if($cfg && $cfg->showRelatedTickets()) {
                    $navs['tickets']=array('desc'=>sprintf(__('My&nbsp;Tickets&nbsp;(%d)'),$user->getNumTickets()),
                                           'href'=>'tickets.php',
                                            'title'=>__('Show all tickets'));
                } else {
                    $navs['tickets']=array('desc'=>__('View&nbsp;Ticket&nbsp;Thread'),
                                           'href'=>sprintf('tickets.php?id=%d',$user->getTicketID()),
                                           'title'=>__('View ticket status'));
                }
            } else {
                $navs['status']=array('desc'=>__('Check Ticket Status'),'href'=>'view.php','title'=>'');
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
