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

print "before delete note:\n";
print "Found " . count($nodebooklist) . " notebooks\n";
foreach ($nodebooklist as $notebook) {
    
    print "* " . $notebook->name . "\n";

    $filter = new NoteStore\NoteFilter();
    $filter->notebookGuid = $notebook->guid;

    //show max 10 notes of each notebook from 1st
    $notelist = $handle->queryNote($filter, 0, 10);
    $notes = $notelist->notes;
    print "note of notebook ".$notebook->name."\n";
    foreach ($notes as $key => $note){
      
        print "    * ".$note->title;
        print "\n";
        if($key == 0){
            $deleteNoteGuid = $note->guid ;
        }
    }
    

}
$counts = $handle->deleteNote($deleteNoteGuid);
print("\ndeleting fist note .....($counts)lines\n");

print ("after delete note:\n");
include "testNotelist.php";

?>