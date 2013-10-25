<?php
/*********************************************************************
    display_open_topics.php

    Displays a block of the last X number of open tickets.

    Neil Tozier <tmib@tmib.net>
    Copyright (c)  2010-2013
    For use with osTicket version 1.7ST (http://www.osticket.com)

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See osTickets's LICENSE.TXT for details.
**********************************************************************/

// The columns that you want to collect data for from the db
$columns = "ticket.name, ticket.subject, ticket.created, ticket.updated, ticket.priority_id, priority.priority_desc, priority.priority_color, department.dept_name";

// The maximum amount of open tickets that you want to display.
$limit ='30';

// mysql query.  The columns tha
$query = "SELECT $columns
			 FROM ost_ticket ticket
			 LEFT JOIN (ost_help_topic help_topic, ost_ticket_priority priority, ost_department department) ON (ticket.topic_id = help_topic.topic_id AND ticket.priority_id = priority.priority_id AND ticket.dept_id = department.dept_id)
			 WHERE ticket.status != 'closed' AND help_topic.ispublic = 1 AND department.ispublic = 1
			 ORDER BY ticket.priority_id DESC,ticket.updated DESC
			 LIMIT 0,$limit";

if($result=mysql_query($query)) { 
  $num = mysql_numrows($result);
}
?>

<div id="openticks">

<?php

if ($num >> 0) {

// table headers, if you add or remove columns edit this
echo "<table><thead><tr>";
echo "<td><b>Name</b></td><td><b>Issue (Priority)</b></td><td><b>Dept.</b></td><td><b>Opened on</b></td><td><b>Last Update</b></td></tr></thead><tbody>";

$i=0;
while ($i < $num) {
 
 // You will need one line below for each column name that you collect and want to display.
 // If you are unfamiliar with php its  essentially $uniqueVariable = mysql junk ( columnName );
 // Just copy one of the lines below and change the $uniqueVariable and columnName
 $name = mysql_result($result,$i,"ticket.name");
 $subject = mysql_result($result,$i,"ticket.subject");
 $created = mysql_result($result,$i,"ticket.created");
 $updated = mysql_result($result,$i,"ticket.updated");
// $agency = mysql_result($result,$i,"agency");
 $priority  = mysql_result($result,$i,"priority.priority_desc");
 $prioritycolor  = mysql_result($result,$i,"priority.priority_color");
 $deptname = mysql_result($result,$i,"department.dept_name");
 
 // if no update say so
 if ($updated == '0000-00-00 00:00:00') {
   $updated = 'no update yet';
 }
 
  // look up priority and display proper name
  // mysql query.
//  $getprioritydesc = "SELECT priority_desc FROM ost_ticket_priority WHERE priority_id = $priority_id";
//  $prioritydescresult=mysql_query($getprioritydesc);
//  $priority = mysql_result($prioritydescresult,0,"priority_desc");
	mysql_close();

	// change row back ground color to make more readable
	if(($i % 2) == 1)  //odd
      {$bgcolour = '#F6F6F6';}
  else   //even
      {$bgcolour = '#FEFEFE';}
 
 //populate the table with data
 echo "<tr style='background-color:$bgcolour;'><td class='name'>$name</td>"
     ."<td class='subject'>$subject <span style='background-color:$prioritycolor;'>($priority)</span></td>"
     ."<td class='dept'>$deptname</td>"
     ."<td class='created'>$created</td>"
     ."<td class='updated'>$updated</td></tr>";
 
 	++$i;
}
echo "</tbody></table>";
}

else {
 echo "<p style='text-align:center;'><span id='msg_warning'>There are no tickets open at this time.</span></p>";
}
?>

</div>
