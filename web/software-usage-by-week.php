<?php
# Copyright 2006, 2007, 2008 Ohio Supercomputer Center
# Revision info:
# $HeadURL: http://svn.osc.edu/repos/pbstools/releases/pbstools-2.0/web/software-usage-by-week.php $
# $Revision: 243 $
# $Date: 2008-04-11 12:00:07 -0400 (Fri, 11 Apr 2008) $
require_once 'dbutils.php';
require_once 'page-layout.php';
require_once 'metrics.php';
require_once 'site-specific.php';

# accept get queries too for handy command-line usage:  suck all the
# parameters into _POST.
if (isset($_GET['system']))
  {
    $_POST = $_GET;
  }

$title = "Software usage by week";
if ( isset($_POST['system']) )
  {
    $title .= " on ".$_POST['system'];
  }
if ( isset($_POST['start_date']) && isset($_POST['end_date']) && $_POST['start_date']==$_POST['end_date'] && 
     $_POST['start_date']!="" )
  {
    $title .= " started on ".$_POST['start_date'];
  }
 else if ( isset($_POST['start_date']) && isset($_POST['end_date']) && $_POST['start_date']!=$_POST['end_date'] && 
	   $_POST['start_date']!="" &&  $_POST['end_date']!="" )
   {
     $title .= " started between ".$_POST['start_date']." and ".$_POST['end_date'];
   }
 else if ( isset($_POST['start_date']) && $_POST['start_date']!="" )
   {
     $title .= " started after ".$_POST['start_date'];
   }
 else if ( isset($_POST['end_date']) && $_POST['end_date']!="" )
   {
     $title .= " started before ".$_POST['end_date'];
   }
page_header($title);

# list of software packages
$packages=software_list();

# regular expressions for different software packages
$pkgmatch=software_match_list();

$keys = array_keys($_POST);
if ( isset($_POST['system']) )
  {
    $db = db_connect();
    foreach ($keys as $key)
      {
	if ( $key!='system' && $key!='start_date' && $key!='end_date' )
	  {
	    echo "<H3><CODE>".$key."</CODE></H3>\n";
	    $sql = "SELECT ".xaxis_column("week").", COUNT(jobid) AS jobcount, SUM(nproc*TIME_TO_SEC(walltime))/3600.0 AS cpuhours, SUM(TIME_TO_SEC(cput))/3600.0 AS cpuhours_alt, COUNT(DISTINCT(username)) AS users, COUNT(DISTINCT(groupname)) AS groups FROM Jobs WHERE system LIKE '".$_POST['system']."' AND ( ";
	    if ( isset($pkgmatch[$key]) )
	      {
		$sql .= $pkgmatch[$key];
	      }
	    else
	      {
		$sql .= "script LIKE '%".$key."%' OR software LIKE '%".$key."%'";
	      }
	    $sql .= " ) AND ( ".dateselect("start",$_POST['start_date'],$_POST['end_date'])." ) GROUP BY week;";
            #echo "<PRE>".htmlspecialchars($sql)."</PRE>";
	    $result = db_query($db,$sql);
	    echo "<TABLE border=1>\n";
	    echo "<TR><TH>week</TH><TH>jobcount</TH><TH>cpuhours</TH><TH>cpuhours_alt</TH><TH>users</TH><TH>groups</TH></TR>\n";
	    while ($result->fetchInto($row))
	      {
		$rkeys=array_keys($row);
		echo "<TR>";
		foreach ($rkeys as $rkey)
		  {
		    $data[$rkey]=array_shift($row);
		    echo "<TD align=\"right\"><PRE>".$data[$rkey]."</PRE></TD>";
		  }
		echo "</TR>\n";
	      }
	    echo "</TABLE>\n";
	  }
      }
    db_disconnect($db);
    bookmarkable_url();
  }
else
  {
    begin_form("software-usage-by-week.php");

    system_chooser();
    date_fields();

    checkboxes_from_array("Packages",$packages);

    end_form();
  }

page_footer();
?>