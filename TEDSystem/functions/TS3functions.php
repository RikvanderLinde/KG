<?php

/* Developed by TB */

function ConnectTS3()
{
    include('../settings.php');
    global $bot_name;
    global $bot_username;
    global $bot_password;
    return TeamSpeak3::factory("serverquery://" . $bot_username . ":" . $bot_password . "@ts.konvictgaming.com:10011/?server_port=9987&blocking=0&nickname=" . $bot_name);
}

function PromoteMemberTS3($clientUID)
{
    $ts3_VirtualServer = ConnectTS3();
    try {$client = $ts3_VirtualServer->clientGetNameByUid($clientUID);}catch(Exception $e){echo "Something went wrong, contact a technician.\nError code:2914";}

    try {$member_ServerGroup = $ts3_VirtualServer->serverGroupGetByName("Member");}catch(Exception $e){echo "Something went wrong, contact a technician.\nError code:2915";}
    try {$member_ServerGroup->clientAdd($client['cldbid']);}catch(Exception $e){echo "Something went wrong, contact a technician.\nError code:2916";}

    try {$trial_ServerGroup = $ts3_VirtualServer->serverGroupGetByName("Trial");}catch(Exception $e){echo "Something went wrong, contact a technician.\nError code:2917";}
    try {$trial_ServerGroup->clientDel($client['cldbid']);}catch(Exception $e){echo "Something went wrong, contact a technician.\nError code:2918";}
}

function DemoteFailedTS3($clientUID)
{
    $ts3_VirtualServer = ConnectTS3();

    try {$client = $ts3_VirtualServer->clientGetNameByUid($clientUID);}catch(Exception $e){echo "Something went wrong, contact a technician.\nError code:2921";}

    try {$failedtrial_ServerGroup = $ts3_VirtualServer->serverGroupGetByName("Failed Trial");}catch(Exception $e){echo "Something went wrong, contact a technician.\nError code:2922";}
    try {$failedtrial_ServerGroup->clientAdd($client['cldbid']);}catch(Exception $e){echo "Something went wrong, contact a technician.\nError code:2923";}

    try {$trial_ServerGroup = $ts3_VirtualServer->serverGroupGetByName("Trial");}catch(Exception $e){echo "Something went wrong, contact a technician.\nError code:2926";}
    try {$trial_ServerGroup->clientDel($client['cldbid']);}catch(Exception $e){echo "Something went wrong, contact a technician.\nError code:2927";}

}