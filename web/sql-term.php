<?php
# Copyright 2006 Ohio Supercomputer Center
# Revision info:
# $HeadURL: http://svn.osc.edu/repos/pbstools/releases/pbstools-2.0/web/sql-term.php $
# $Revision: 257 $
# $Date: 2008-07-02 16:19:06 -0400 (Wed, 02 Jul 2008) $
require_once 'dbutils.php';
require_once 'page-layout.php';

page_header("PHP SQL Terminal");

echo "<FORM method=\"POST\" action=\"sql-term.php\">\n";
echo "<TEXTAREA name=\"sql\" cols=\"80\" rows=\"5\">\n";
if ( isset($_POST['sql']) )
  {
    echo stripslashes($_POST['sql']);
  }
echo "</TEXTAREA>\n<BR>\n";
echo "<INPUT type=\"submit\">\n<INPUT type=\"reset\">\n</FORM>\n";

if ( isset($_POST['sql']) )
  {
    $db = db_connect();
    if ( DB::isError($db) )
      {
	die ($db->getMessage());
      }
    $result = db_query($db,stripslashes($_POST['sql']));
    if ( DB::isError($db) )
      {
	die ($db->getMessage());
      }
    else
      {
	echo "<TABLE border=1>\n";
	while ($result->fetchInto($row))
	  {
	    echo "<TR valign=\"top\">";
	    $keys=array_keys($row);
	    foreach ($keys as $key)
	      {
		$data=array_shift($row);
		echo "<TD><PRE>".htmlspecialchars($data)."</PRE></TD>";
	      }
	    echo "</TR>\n";
	  }
	echo "</TABLE>\n";
      }
    db_disconnect($db);
  }

page_footer();
?>