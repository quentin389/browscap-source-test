<?php

function include_browscap($folder, $ini_file, $class_name)
{
  require_once $folder . $class_name . '.php';
  $browscap = new $class_name($folder);
  $browscap->localFile = $folder . $ini_file;
  $browscap->updateMethod = $class_name::UPDATE_LOCAL;
  $browscap->updateCache();
  return $browscap;
}

function get_info($class, $user_agent)
{
  $result = $class->getBrowser($user_agent, true);
  if ('Default Browser' == $result['Browser']) return '--';
  $string = $result['Browser'] . ' ' . trim(trim($result['Version'], '0'), '.') . ', ';
  if ('unknown' != $result['Platform']) $string .= $result['Platform'] . ' ' . trim(trim($result['Platform_Version'], '0'), '.') . ', ';
  if ($result['Win64']) $string .= '64 bit ';
  if ($result['Alpha']) $string .= 'alpha ';
  if ($result['Beta']) $string .= 'beta ';
  if ($result['isMobileDevice']) $string .= 'mobile ';
  if ($result['isSyndicationReader']) $string .= 'RSS reader ';
  if ($result['Crawler']) $string .= 'bot ';
  return trim($string, ' ,');
}

function compare_info(array $list)
{
  $compare = array_unique($list);
  if (1 == count($compare)) return false;
  return $list;
}

$release_order = include_browscap('release-order/', 'php_browscap_release.ini', 'RO_Browscap');
$release_raw = include_browscap('release-raw/', 'php_browscap_release.ini', 'RR_Browscap');
$beta_order = include_browscap('beta-order/', 'php_browscap_beta.ini', 'BO_Browscap');
$beta_raw = include_browscap('beta-raw/', 'php_browscap_beta.ini', 'BR_Browscap');

$raw_agents = explode("\n", file_get_contents('user-agent-examples.txt'));

$user_agents = array();
$ok_count = 0;
$diff_count = 0;

foreach ($raw_agents as $ua_string)
{
  $results = compare_info(array(
    'ro' => get_info($release_order, $ua_string),
    'rr' => get_info($release_raw, $ua_string),
    'bo' => get_info($beta_order, $ua_string),
    'br' => get_info($beta_raw, $ua_string),
  ));
  if (!$results) $ok_count++;
  else {
    $diff_count++;
    $user_agents[$ua_string] = $results;
  }
}
?>

<h3><?=$ok_count?> user agents identical, <?=$diff_count?> with differences</h3> 

<table>
  <tr>
    <th>user agent</th>
    <th>5020 ordered</th>
    <th>5020 raw</th>
    <th>5021-b7 ordered</th>
    <th>5021-b7 raw</th>
    <th></th>
  </tr>
  <? foreach ($user_agents as $ua_name => $ua_data): ?>
  <tr>
    <td class="name"><?=$ua_name?></td>
    <td class="ro"><?=$ua_data['ro']?></td>
    <td class="rr"><?=$ua_data['rr']?></td>
    <td class="bo"><?=$ua_data['bo']?></td>
    <td class="br"><?=$ua_data['br']?></td>
  </tr>
  <? endforeach; ?>
</table>

<style>
table { border-collapse: collapse; font-size: 12px; }
table tr:hover { background-color: yellow; }
table th { padding: 15px; }
table td { border: 1px solid gray; padding: 5px 10px; white-space: nowrap; }
table td.name { white-space: normal; }
table .rr { color: gray; }
</style>

