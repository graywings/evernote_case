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
$i = '0';
$nodebooklist = $handle->queryNotebook();
print ("before expunge notebook:\n");
print "Found " . count($nodebooklist) . " notebooks\n";
foreach ($nodebooklist as $notebook) {
    print "    * " . $notebook->name . "\n";
    print "    * " . $notebook->guid . "\n";
    $i++;
    if($i=='1')
    {
       $notebookGuid = $notebook->guid;
    }
}

//expund an notebook and then show the booklist 
//$bookname = 'testchy1';
//$notebookGuid = '9f479469-ba25-443a-b310-6d631014f1ad';
$add_result = $handle->expungeNotebook($notebookGuid);

$nodebooklist = $handle->queryNotebook();
print ("after expunge notebook:\n");

print "Found " . count($nodebooklist) . " notebooks\n";
foreach ($nodebooklist as $notebook) {
    print "    * " . $notebook->name . "\n";
    print "    * " . $notebook->guid . "\n";
}

?>