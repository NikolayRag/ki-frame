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
	KiFrame
		Core init and entry.
	KiHandler
		Error, exception and exit handler, form actual headers and return data.
	KiRoute
		Routing matrix, URLs to declared context generators.
	KiAuth
		User authorisation, by log/pass or by social oAuth logon.

	KiRights
		Defined right support.
	KiGroup
		User group assignment support.
	KiData
		Database relation model.

	KiConst
		Global constants.
	KiURL
		URL parser.
	KiAgent
		Browser, os, device detector and classifier.
	KiSQL
		SQL wrapper.
	KiDict
		Multilingual dictionary.

	KiLoad
		Upload and Download support.

suit
	basic frontend wrap - all direct addressed default page, logon routines, 404
pack
	basic complete template - ajax logon, page structure, style
live
	working basic cms
*/
include(__dir__ .'/KiFrame.php');

?>
