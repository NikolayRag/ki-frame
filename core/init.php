<?
/*
Entry point to KiFrame.
Used as a singletone class.

Core modules:
+- db, data layer
+ error handler and shutdown
+- authoring, rights
+ routing
Support modules:
+ constants
+ url
+ agent
- dictionary
- caching



core
	basic routing, authoring, error handle and support classes - URL, agent, constants, SQL, dictionary
suit
	basic frontend wrap - all direct addressed default page, logon routines, 404
pack
	basic complete template - ajax logon, page structure, style
live
	working basic cms
*/
include(__dir__ .'/KiFrame.php');

?>
