<?php

require_once __DIR__."/epalsApi/vendor/autoload.php" ;

use ePals\UserAttribute;
use ePals\Base\User;
use ePals\EvernoteHandler;


use EDAM\Types\Data, EDAM\Types\Note, EDAM\Types\Notebook, EDAM\Types\Resource, EDAM\Types\ResourceAttributes,
  EDAM\NoteStore;
use EDAM\Error\EDAMUserException, EDAM\Error\EDAMErrorCode;
use Evernote\Client;



$userAttr = new UserAttribute();
$userAttr->set_ElasticSearch_Server("http://apidev.dev.epals.com:9200");
$userAttr->set_SIS_Server("http://dev01.neuedu.dev.ec2.epals.net:8080/sis/");
//$userAttr->loadUserAttribute("zhangxiaofan1@epals.com");
$userAttr->initEvernoteHandler("S=s1:U=8e46f:E=14cbae274e8:C=145633148eb:P=81:A=jiatq:V=2:H=fd3b72207e9e6f1de899b0f2828b8a59");
                               

$nodebooklist = $userAttr->getEvernoteHandler()->queryNotebook();


print "Found " . count($nodebooklist) . " notebooks\n";
foreach ($nodebooklist as $notebook) {
    print "* " . $notebook->name . "\n";


    $filter = new EDAM\NoteStore\NoteFilter();
    $filter->notebookGuid = $notebook->guid;

    //show max 10 notes of each notebook from 1st
    /*
    $notelist = $userAttr->getEvernoteHandler()->queryNote($filter, 0, 10);
    $notes = $notelist->notes;
    print "note of notebook ".$notebook->name."\n";
    foreach ($notes as $key => $note){
      
        print "    * ".$note->title;
        print "\n";
    }
    */
    
}

//print_r($userAttr);


?>
