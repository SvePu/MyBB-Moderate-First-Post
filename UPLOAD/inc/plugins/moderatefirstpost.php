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
    $plugins->add_hook("admin_config_plugins_deactivate_commit", 'moderatefirstpost_delete_plugin');
    $plugins->add_hook("admin_config_settings_begin",'moderatefirstpost_settings_page');
    $plugins->add_hook("admin_page_output_footer",'moderatefirstpost_settings_peeker');
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
    global $mybb, $db, $plugins_cache, $lang;
    $lang->load('moderatefirstpost', true);

    $info = array(
        'name'          => $db->escape_string($lang->moderatefirstpost),
        'description'   => $db->escape_string($lang->moderatefirstpost_desc),
        "website"       => "https://github.com/SvePu/MyBB-Moderate-First-Post",
        "author"        => "SvePu",
        "authorsite"    => "https://github.com/SvePu",
        "version"       => "1.2",
        "codename"      => "moderatefirstpost",
        "compatibility" => "18*"
    );

    if(is_array($plugins_cache) && is_array($plugins_cache['active']) && $plugins_cache['active']['moderatefirstpost'])
    {
        $gid_result = $db->simple_select('settinggroups', 'gid', "name = 'moderatefirstpost'", array('limit' => 1));
        $settings_group = $db->fetch_array($gid_result);
        if(!empty($settings_group['gid']))
        {
            $info['description'] = "<span class=\"float_right\"><a href=\"index.php?module=config-settings&amp;action=change&amp;gid=".$settings_group['gid']."\"><img src=\"./styles/default/images/icons/custom.png\" title=\"".$db->escape_string($lang->setting_group_moderatefirstpost)."\" alt=\"settings_icon\" width=\"16\" height=\"16\" /></a></span>" .$info['description'];
        }
    }

    $installed_func = "moderatefirstpost_is_installed";

    if(function_exists($installed_func) && $installed_func() != true)
    {
        $info['description'] = "<span class=\"float_right\"><a href=\"index.php?module=config-plugins&amp;action=deactivate&amp;plugin=moderatefirstpost&amp;delete=1&amp;my_post_key={$mybb->post_code}\"><img src=\"./styles/default/images/icons/delete.png\" title=\"".$db->escape_string($lang->delete_moderatefirstpost_link)."\" alt=\"settings_icon\" width=\"16\" height=\"16\" /></a></span>" .$info['description'];
    }

    return $info;
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
        ),
        'moderatefirstpost_forums' => array(
            'title' => $db->escape_string($lang->setting_moderatefirstpost_forums),
            'description' => $db->escape_string($lang->setting_moderatefirstpost_forums_desc),
            'optionscode' => 'forumselect',
            'value' => '-1',
            'disporder' => 2
        ),
        'moderatefirstpost_forums_type' => array(
            'title' => $db->escape_string($lang->setting_moderatefirstpost_forums_type),
            'description' => $db->escape_string($lang->setting_moderatefirstpost_forums_type_desc),
            'optionscode' => 'radio \n1='. $db->escape_string($lang->setting_moderatefirstpost_forums_type_1). '\n2='. $db->escape_string($lang->setting_moderatefirstpost_forums_type_2),
            'value' => '1',
            'disporder' => 3
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

function moderatefirstpost_settings_page()
{
    global $db, $mybb, $lang, $mfp_settings_peeker;
    $lang->load('moderatefirstpost', true);
    $query = $db->simple_select("settinggroups", "gid", "name='moderatefirstpost'", array('limit' => 1));
    $group = $db->fetch_array($query);
    $mfp_settings_peeker = ($mybb->input["gid"] == $group["gid"]) && ($mybb->request_method != "post");
}

function moderatefirstpost_settings_peeker()
{
    global $mfp_settings_peeker;
    if($mfp_settings_peeker)
    {
        echo '<script type="text/javascript">
        $(document).ready(function(){
            new Peeker($(".setting_moderatefirstpost_enable"), $("#row_setting_moderatefirstpost_forums, #row_setting_moderatefirstpost_forums_type"), 1, true),
            new Peeker($(".setting_moderatefirstpost_forums_forums_groups_check"), $("#row_setting_moderatefirstpost_forums_type"), "custom", true);
        });
        </script>';
    }
}

function moderatefirstpost_run()
{
    global $mybb, $fid, $lang;
    $lang->load('moderatefirstpost');

    if(!$mybb->user['uid'] || $mybb->usergroup['canmodcp'] == 1 || $mybb->settings['moderatefirstpost_enable'] != 1 || $mybb->settings['moderatefirstpost_forums'] == '')
    {
        return;
    }

    if($mybb->settings['moderatefirstpost_forums'] == '-1' && $mybb->user['postnum'] < 1)
    {
        $moderatefirstpost = true;
    }
    elseif($mybb->settings['moderatefirstpost_forums'] != '-1' && in_array($fid, explode(',', $mybb->settings['moderatefirstpost_forums'])))
    {
        global $db;
        $mfp_cache=array();
        $query = $db->simple_select("posts", "fid, pid", "fid IN ({$mybb->settings['moderatefirstpost_forums']}) AND uid='{$mybb->user['uid']}' AND visible=1");
        while($result=$db->fetch_array($query))
        {
            $mfp_cache[$result['fid']][] = $result['pid'];
        }
        if(!array_key_exists($fid, $mfp_cache))
        {
            $moderatefirstpost = true;
            if($mybb->settings['moderatefirstpost_forums_type'] != "2")
            {
                if(!empty($mfp_cache))
                {
                    $moderatefirstpost = false;
                }
            }
        }
        unset($mfp_cache);
    }
    else
    {
        $moderatefirstpost = false;
    }

    if($moderatefirstpost)
    {
        $mybb->user['moderateposts'] = 1;
        $lang->moderation_user_posts = $lang->moderation_user_posts . $lang->moderatefirstpost_moderation_user_posts;
    }
}

function moderatefirstpost_delete_plugin()
{
    global $mybb;
    if (!$mybb->get_input('delete'))
    {
        return;
    }

    if($mybb->get_input('delete') == 1)
    {
        global $lang;
        $lang->load('moderatefirstpost', true);
        $codename = str_replace('.php', '', basename(__FILE__));

        $installed_func = "{$codename}_is_installed";

        if(function_exists($installed_func) && $installed_func() != false)
        {
            flash_message($lang->moderatefirstpost_still_installed, 'error');
            admin_redirect('index.php?module=config-plugins');
            exit;
        }

        if($mybb->request_method != 'post')
        {
            global $page;
            $page->output_confirm_action("index.php?module=config-plugins&amp;action=deactivate&amp;plugin={$codename}&amp;delete=1&amp;my_post_key={$mybb->post_code}", $lang->moderatefirstpost_delete_confirm_message, $lang->moderatefirstpost_delete_confirm);
        }

        if(!isset($mybb->input['no']))
        {
            global $message;

            if(($handle = @fopen(MYBB_ROOT . "inc/plugins/pluginstree/" . $codename . ".csv", "r")) !== FALSE)
            {
                while(($pluginfiles = fgetcsv($handle, 1000, ",")) !== FALSE)
                {
                    foreach($pluginfiles as $file)
                    {
                        $filepath = MYBB_ROOT.$file;

                        if(@file_exists($filepath))
                        {
                            if(is_file($filepath))
                            {
                                @unlink($filepath);
                            }
                            elseif(is_dir($filepath))
                            {
                                $dirfiles = array_diff(@scandir($filepath), array('.','..'));
                                if(empty($dirfiles))
                                {
                                    @rmdir($filepath);
                                }
                            }
                            else
                            {
                                continue;
                            }
                        }
                    }
                }
                @fclose($handle);
                @unlink(MYBB_ROOT . "inc/plugins/pluginstree/" . $codename . ".csv");

                $message = $lang->moderatefirstpost_delete_message;
            }
            else
            {
                flash_message($lang->moderatefirstpost_undelete_message, 'error');
                admin_redirect('index.php?module=config-plugins');
                exit;
            }
        }
        else
        {
            admin_redirect('index.php?module=config-plugins');
            exit;
        }
    }
}
