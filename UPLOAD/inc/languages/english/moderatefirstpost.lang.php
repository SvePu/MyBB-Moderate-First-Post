<?php
/**
 * MyBB 1.8 Plugin - MyBB Moderate First Post
 * MyBB 1.8 Langpack English
 *
 */

// ACP
$l['moderatefirstpost'] = "Moderate First Post";
$l['moderatefirstpost_desc'] = "This plugin forces the moderation of the first post from members. ";

// ACP Settings
$l['setting_group_moderatefirstpost'] = "Moderate First Post Settings";
$l['setting_group_moderatefirstpost_desc'] = "Settings of Moderate First Post plugin";
$l['setting_moderatefirstpost_enable'] = "Activate Moderate First Post plugin?";
$l['setting_moderatefirstpost_enable_desc'] = "Select YES to activate the functions of the Moderate First Post plugin!";
$l['setting_moderatefirstpost_forums'] = "Forum Select";
$l['setting_moderatefirstpost_forums_desc'] = "Select the forums where the first user post should be moderated!";
$l['setting_moderatefirstpost_forums_type'] = "Type of Moderation";
$l['setting_moderatefirstpost_forums_type_desc'] = "Select the type of moderation of the first user post within the forums selected above. (This setting is not important if there is only one selected forum!)";
$l['setting_moderatefirstpost_forums_type_1'] = "Universal moderation independent of the forum";
$l['setting_moderatefirstpost_forums_type_2'] = "Individual moderation in each forum";

// Forum
$l['moderatefirstpost_moderation_user_posts'] = "<br/>We have preset this procedure for spam protection reasons.<br/>As soon as your first post is visible in the forum, this control option is automatically deactivated.";

// Plugin Delete Action
$l['delete_moderatefirstpost_link'] = "Delete plugin files";
$l['moderatefirstpost_delete_confirm'] = "Run plugin files deleting";
$l['moderatefirstpost_delete_confirm_message'] = "Do you want to remove the plugin files permanently and completely from the server?";
$l['moderatefirstpost_delete_message'] = "The plugin files have been completely removed from the server.";
$l['moderatefirstpost_undelete_message'] = "The plugin files could not be deleted because there is a problem with the required CSV file!";
$l['moderatefirstpost_still_installed'] = "The plugin is still installed and must be uninstalled before deleting it!";
