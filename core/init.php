<?
/*
Motivation:

KiFrame is developed as a second-edge framework to suite own needs, formulated without strict reference to any existing frameworks, though corresponding to widely used stable standarts and method, which have passed proof-of-use.



KiFrame is mainly used as a singletone class.

Bundles suppliead are:


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
	basic frontend wrap for non-ajax flow
		default page
		404
		logon routines
		upload by form routines


pack
	ajax template
		logon
		upload
		download
		page templates
		style

live
	working cms
*/
include(__dir__ .'/KiFrame.php');

?>
