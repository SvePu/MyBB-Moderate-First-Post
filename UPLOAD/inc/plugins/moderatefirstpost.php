<?php
/**
 * MyBB 1.8 Plugin - MyBB Moderate First Post
 * Copyright 2022 SvePu, All Rights Reserved
 *
 * Website: https://github.com/SvePu/MyBB-Moderate-First-Post
 * License: https://github.com/SvePu/MyBB-Moderate-First-Post/blob/main/LICENSE
 *
 */

if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.");
}

if(defined('IN_ADMINCP'))
{
    $plugins->add_hook('admin_config_settings_begin', 'moderatefirstpost_acp_lang');
}
else
{
    $plugins->add_hook('newreply_do_newreply_start', 'moderatefirstpost_run');
    $plugins->add_hook('newthread_do_newthread_start', 'moderatefirstpost_run');
    $plugins->add_hook('newreply_start', 'moderatefirstpost_run');
    $plugins->add_hook('newthread_start', 'moderatefirstpost_run');
    $plugins->add_hook('showthread_start', 'moderatefirstpost_run');
}


function moderatefirstpost_info()
{
    global $db, $lang;
    $lang->load('moderatefirstpost', true);

    return array(
        'name'          => $db->escape_string($lang->moderatefirstpost),
        'description'   => $db->escape_string($lang->moderatefirstpost_desc),
        "website"       => "https://github.com/SvePu/MyBB-Moderate-First-Post",
        "author"        => "SvePu",
        "authorsite"    => "https://github.com/SvePu",
        "version"       => "1.0",
        "codename"      => "moderatefirstpost",
        "compatibility" => "18*"
    );
}

function moderatefirstpost_install()
{
    global $db, $lang;
    $lang->load('moderatefirstpost', true);

    $query = $db->simple_select("settinggroups", "COUNT(*) AS disporder");
    $disporder = $db->fetch_field($query, "disporder");

    $setting_group = array(
        'name' => 'moderatefirstpost',
        "title" => $db->escape_string($lang->setting_group_moderatefirstpost),
        "description" => $db->escape_string($lang->setting_group_moderatefirstpost_desc),
        'disporder' => $disporder+1,
        'isdefault' => 0
    );

    $gid = $db->insert_query("settinggroups", $setting_group);

    $setting_array = array(
        'moderatefirstpost_enable' => array(
            'title' => $db->escape_string($lang->setting_moderatefirstpost_enable),
            'description' => $db->escape_string($lang->setting_moderatefirstpost_enable_desc),
            'optionscode' => 'yesno',
            'value' => 1,
            'disporder' => 1
        )
    );

    foreach($setting_array as $name => $setting)
    {
        $setting['name'] = $name;
        $setting['gid'] = $gid;
        $db->insert_query('settings', $setting);
    }

    rebuild_settings();
}

function moderatefirstpost_is_installed()
{
    global $mybb;
    if(isset($mybb->settings['moderatefirstpost_enable']))
    {
        return true;
    }
    return false;
}

function moderatefirstpost_uninstall()
{
    global $db, $mybb;

    $query = $db->simple_select("settinggroups", "gid", "name='moderatefirstpost'");
    $gid = $db->fetch_field($query, "gid");
    if(!$gid)
    {
        return;
    }
    $db->delete_query("settinggroups", "name='moderatefirstpost'");
    $db->delete_query("settings", "gid=$gid");
    rebuild_settings();
}

function moderatefirstpost_activate()
{

}

function moderatefirstpost_deactivate()
{

}

function moderatefirstpost_acp_lang()
{
    global $lang;
    $lang->load('moderatefirstpost', true);
}

function moderatefirstpost_run()
{
    global $mybb, $lang;
    $lang->load('moderatefirstpost');

    if(!$mybb->user['uid'] || $mybb->settings['moderatefirstpost_enable'] != 1)
    {
        return;
    }

    if($mybb->user['postnum'] < 1 && $mybb->usergroup['canmodcp'] != 1)
    {
        $mybb->user['moderateposts'] = 1;
        $lang->moderation_user_posts = $lang->moderation_user_posts . $lang->moderatefirstpost_moderation_user_posts;
    }
}
