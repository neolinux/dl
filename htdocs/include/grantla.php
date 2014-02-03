<?php
require_once("pages.php");
require_once("style/include/style.php");
$act = "glista";
$ref = pageLinkAct();
pageHeader();

if(isset($_REQUEST["purge"]) && !empty($_REQUEST["sel"]))
{
  $list = array();
  $sel = &$_REQUEST["sel"];
  if(!is_array($sel)) $sel = array($sel);

  // purge immediately
  foreach($sel as $id)
  {
    if(!isGrantId($id)) continue;
    $sql = "SELECT * FROM \"grant\" WHERE id = " . $db->quote($id);
    $DATA = $db->query($sql)->fetch();
    if($DATA === false) continue;

    // actually purge the grant
    $list[] = htmlEntUTF8(grantStr($DATA));
    grantPurge($DATA, false);
  }

  if(count($list))
    infoMessage(T_("Purged"), $list);
}

// list active grants
$sql = 'SELECT g.*, u.name AS "user" FROM "grant" g'
    . ' LEFT JOIN "user" u ON u.id = g.user_id'
    . ' ORDER BY time DESC';

?>
<form action="<?php echo $ref; ?>" method="post">
  <table class="sortable" id="allgrants">
    <thead>
      <tr>
        <th><input class="element checkbox" type="checkbox" onclick="selectAll(this.checked);"/></th>
        <th></th>
        <th></th>
        <th data-sort="string"><?php echo T_("Grant"); ?></th>
        <th data-sort="string"><?php echo T_("User"); ?></th>
        <th data-sort="int" class="sorting-desc"><?php echo T_("Date"); ?></th>
        <th data-sort="int"><?php echo T_("Expiration"); ?></th>
      </tr>
    </thead>
    <tbody>
<?php

foreach($db->query($sql) as $DATA)
{
  if(isGrantExpired($DATA)) continue;

  $our = ($DATA["user_id"] == $auth["id"]);
  $class = "file expanded " . $DATA['id'];
  if($our) $class .= " our";
  echo "<tr class=\"$class\">";

  // selection
  echo "<td><input class=\"element checkbox\" type=\"checkbox\" name=\"sel[]\" value=\"" . $DATA['id'] . "\"/></td>";

  // upload
  echo '<td><a href="' . grantUrl($DATA) . '">'
    . '<img title="' . T_("Upload")
    . '" src="style/static/upload.png"/></a></td>';

  // delete
  echo "<td><a href=\"" . pageLinkAct(array('purge' => null, 'sel' => $DATA['id'])) . "\">"
    . "<img title=\"" . T_("Purge")
    . "\" src=\"style/static/cross.png\"/></a></td>";

  // name
  echo '<td class="ticketid">' . htmlEntUTF8($DATA['id']) . '</td>';

  // user
  echo "<td>" . htmlEntUTF8($DATA["user"]) . "</td>";

  // date
  echo '<td data-sort-value="' . $DATA["time"]
      . '">' . date("d/m/Y T", $DATA["time"]) . '</td>';

  // expire
  $expStr = grantExpiration($DATA, $expVal);
  echo "<td data-sort-value=\"$expVal\">$expStr</td>";

  echo "</tr>";
}

?>
    </tbody>
  </table>

  <ul>
    <li class="buttons">
      <input type="button" value="<?php echo T_("Reload"); ?>" onclick="document.location.reload();"/>
      <input type="reset" value="<?php echo T_("Reset"); ?>"/>
      <input type="button" value="<?php echo T_("Select all"); ?>" onclick="selectAll();"/>
      <input type="submit" name="purge" value="<?php echo T_("Purge selected"); ?>"/>
    </li>
  </ul>
</form>

<?php
pageFooter();
?>