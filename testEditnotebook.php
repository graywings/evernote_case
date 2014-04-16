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
//edit an notebook and then show the booklist 
$handle = new EvernoteHandler(USER_TOKEN);
$i = '0';
$nodebooklist = $handle->queryNotebook();
print ("before edit notebook:\n");
print "Found " . count($nodebooklist) . " notebooks\n";
foreach ($nodebooklist as $notebook) {
    print "    * " . $notebook->name . "\n";
    print "    * " . $notebook->guid . "\n";
    $i++;
    if($i=='1')
    {
       $notebookname = $notebook->name;
       $notebookGuid = $notebook->guid;
    }
}


$bookname = 'testchy55';
print ("change ".$notebookname." to ".$bookname.":\n");
//$notebookGuid = '9f479469-ba25-443a-b310-6d631014f1ad';
$add_result = $handle->editNotebook($notebookGuid, $bookname);
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
    print "success\n";
}

$nodebooklist = $handle->queryNotebook();
print ("after edit notebook:\n");

print "Found " . count($nodebooklist) . " notebooks\n";
foreach ($nodebooklist as $notebook) {
    print "    * " . $notebook->name . "\n";
    print "    * " . $notebook->guid . "\n";
}

?>