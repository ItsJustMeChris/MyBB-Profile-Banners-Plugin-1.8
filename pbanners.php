<?php
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.");
}

$plugins->add_hook('member_profile_start', 'pbanners_profileStart');
$plugins->add_hook('misc_start', 'pbanners_miscPost');

function pbanners_info()
{
	return array(
		"name"			=> "Profile Banners",
		"description"	=> "Add profile banners to user profiles",
		"website"		=> "-",
		"author"		=> "Chris",
		"authorsite"	=> "-",
		"version"		=> "1.0",
		"guid" 			=> "",
		"codename"		=> "pbanners",
		"compatibility" => "*"
	);
}

function pbanners_profileStart()
{
  global $db,$mybb,$templates, $banner,$form;
	$cleanUID = (int) filter_var($mybb->get_input('uid', 1),FILTER_SANITIZE_NUMBER_INT);
	$cleanUID = intval($cleanUID);
	$query = $db->query("SELECT * FROM `mybb_profile_banners` WHERE `uid`=".$cleanUID."");
	if ($db->num_rows($query) >= 1) {
		$query = $db->simple_select("profile_banners", "*", "uid=".$cleanUID."", array(
    		"order_by" => 'uid',
    		"order_dir" => 'DESC',
    		"limit" => 1
		));
		$bannerData = $db->fetch_array($query);
		$profile_banner_url = $bannerData['url'];
		eval('$banner  = "' . $templates->get('pbanners_profile_image_template') . '";');
	}
	if ($mybb->user['uid'] == $mybb->get_input('uid',1)) {
		eval('$form  = "' . $templates->get('pbanners_profile_form_template') . '";');
	}
}

function pbanners_miscPost()
{
	if ($_GET['action'] == "bannerChange")
	{
		global $db,$mybb;
		$cleanUID = (int) filter_var($mybb->user['uid'],FILTER_SANITIZE_NUMBER_INT);
		$cleanUID = intval($cleanUID);
		$cleanURL = $db->escape_string($_POST['bannerURL']);
		$query = $db->query("SELECT * FROM `mybb_profile_banners` WHERE `uid`=".$cleanUID."");
		if ($db->num_rows($query) < 1) {
			$profileBanner = array(
				"uid" => $cleanUID,
				"url" => $cleanURL
			);
			$db->insert_query("profile_banners", $profileBanner);
		} else {
			$update_array = array(
					"uid" => $cleanUID,
					"url" => $cleanURL
			);
			$db->update_query("profile_banners", $update_array, " `uid` = ".$cleanUID."");
		}
 	}
}

function pbanners_install()
{
  global $db;
  $db->query("CREATE TABLE IF NOT EXISTS " . TABLE_PREFIX . "profile_banners (
             `uid` varchar(220) NOT NULL,
             `url` varchar(220) NOT NULL,
             PRIMARY KEY (`uid`)
             ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
}

function pbanners_is_installed()
{
  global $db;
  if($db->table_exists("profile_banners"))
  {
      return true;
  }
  return false;
}

function pbanners_uninstall()
{
  global $db;
  $db->query("DROP TABLE " . TABLE_PREFIX . "profile_banners");
}

function pbanners_activate()
{
	global $db;
  $imageTemplate = '<img id=profileBanner src={$profile_banner_url}/>';
	$formTemplate = '<div id="profileBannerBannerChange">Changer'. "\n".
	'  <form action="misc.php" method="post" id="bannerForm">'."\n".
	'    Banner URL: <input type="text" name="bannerURL"><br>'."\n".
	'    <input type="submit" value="Save">'."\n".
	'  </form>'."\n".
	'</div>' . "\n".
	'<script>'. "\n".
	'  $("#bannerForm").submit(function(e) {'."\n".
	'    var url = "misc.php?action=bannerChange";'."\n".
	'    $.ajax({'."\n".
	'      type: "POST",'."\n".
	'      url: url,'."\n".
	'      data: $("#bannerForm").serialize(), '."\n".
	'      success: function(data)'. "\n".
	'        {'."\n".
	'          alert("Success");'."\n".
	'        }'."\n".
	'      });'."\n".
	'    e.preventDefault();'."\n".
	'  });'."\n".
	'</script>';
	$insert_array = array(
      'title' => 'pbanners_profile_image_template',
      'template' => $db->escape_string($imageTemplate),
      'sid' => '-1',
      'version' => '',
      'dateline' => time()
  );
  $db->insert_query('templates', $insert_array);
	$insert_array = array(
      'title' => 'pbanners_profile_form_template',
      'template' => $db->escape_string($formTemplate),
      'sid' => '-1',
      'version' => '',
      'dateline' => time()
  );
  $db->insert_query('templates', $insert_array);
}

function pbanners_deactivate()
{
	global $db;
	$db->delete_query("templates", "title = 'pbanners_profile_image_template'");
	$db->delete_query("templates", "title = 'pbanners_profile_form_template'");
}
