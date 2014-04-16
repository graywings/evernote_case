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

/*
 * evernote api use case
 */

print ("before create note:\n");
include "testNotelist.php";

//create a new note with title:testTitle2 body:testBody2 
//if want to create new note in a notebook use 4th param.
$newNote = $handle->makeNote("testAddTitle6","testAddBody6",null);

if($newNote){
    print("\ncreate note ".$newNote->title." success.\n");
}else{
    print("\ncreate note faild.\n");
}

print ("after create note:\n");
include "testNotelist.php";
?>