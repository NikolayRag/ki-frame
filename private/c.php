<?
KiCONST::add('DEBUG', 1);


KiCONST::add('PATHS', (object)[ 
	'UPLOAD'=> 'upload/tmp',
	'IMAGE_THUMBS'=> 'upload/thumbs',
	'IMAGE_STORAGE'=> 'upload/images',
]);

//these constants are also read into js


KiCONST::add('USER_GROUPS', (object)[
	'VIEW'=>	1,
	'EDIT'=>	2,
	'ADMIN'=>	4,
	'OPERATE'=>	8
]);

KiCONST::add('ERRRES', (object)[
	'OK'=>	0,
	'EGROUP'=>	1,
	'EOWN'=>	2,
	'ESTEP'=>	4,
	'ETIME'=>	8
]);



KiCONST::add('FLOWSTEP', (object)[
	'NONE'=>	0,

	'PLAN'=>	1,
	'REPORT'=>	2,
	'DELETED'=>	4,

	//aliases for Rights->ban(), not used in db
	'LIST'=>	-1,
	'ADD'=>	-2,
	'UNDELETE'=>	-3,
	'UNPUBLIC'=>	-4,
	'MARK'=>	-6
]);

?>
