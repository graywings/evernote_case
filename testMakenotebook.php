<?php

require_once __DIR__."/vendor/autoload.php";
require_once __DIR__.'/config.php';

use ePals\EvernoteHandler;
use EDAM\Types\Data, EDAM\Types\Note, EDAM\Types\Notebook, EDAM\Types\Resource, EDAM\Types\ResourceAttributes,
  EDAM\NoteStore;
use EDAM\Error\EDAMUserException, EDAM\Error\EDAMErrorCode;
use Evernote\Client;

/*
 * evernote api use case
 */
$handle = new EvernoteHandler(USER_TOKEN);
$nodebooklist = $handle->queryNotebook();
print ("before create notebook:\n");
print "Found " . count($nodebooklist) . " notebooks\n";
foreach ($nodebooklist as $notebook) {
    print "    * " . $notebook->name . "\n";
    print "    * " . $notebook->guid . "\n";
}
//add an notebook and then show the booklist 
$bookname = 'testchy23';

$add_result = $handle->makeNotebook($bookname);

$return_type = gettype($add_result);
if($return_type == 'string')
{
    print $add_result."\n";
}
else if($return_type == 'object')
{
print $add_result->name."\n";
}
else
{
    print $add_result."\n";
}

$nodebooklist = $handle->queryNotebook();
print ("after create notebook:\n");

print "Found " . count($nodebooklist) . " notebooks\n";
foreach ($nodebooklist as $notebook) {
    print "    * " . $notebook->name . "\n";
    print "    * " . $notebook->guid . "\n";
}

?>