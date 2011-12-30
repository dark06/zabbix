<?php
/*
** Zabbix
** Copyright (C) 2000-2011 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/
?>
<?php
$visible		= get_request('visible', array());
$groups			= get_request('groups', array());
$newgroup		= get_request('newgroup', '');
$status			= get_request('status', HOST_STATUS_MONITORED);
$proxy_hostid	= get_request('proxy_hostid', '');
$ipmi_authtype	= get_request('ipmi_authtype', -1);
$ipmi_privilege	= get_request('ipmi_privilege', 2);
$ipmi_username	= get_request('ipmi_username', '');
$ipmi_password	= get_request('ipmi_password', '');
$inventory_mode	= get_request('inventory_mode', HOST_INVENTORY_DISABLED);
$host_inventory = get_request('host_inventory', array());
$templates		= get_request('templates', array());

natsort($templates);

$frmHost = new CFormTable(_('Host mass update'));
$frmHost->setName('host.massupdate');
$frmHost->addVar('go', 'massupdate');

$hosts = $_REQUEST['hosts'];
foreach ($hosts as $id => $hostid) {
	$frmHost->addVar('hosts['.$hostid.']', $hostid);
}

$grp_tb = new CTweenBox($frmHost, 'groups', $groups, 6);
$options = array(
	'output' => API_OUTPUT_EXTEND,
	'editable' => true
);
$all_groups = API::HostGroup()->get($options);
order_result($all_groups, 'name');
foreach ($all_groups as $grp) {
	$grp_tb->addItem($grp['groupid'], $grp['name']);
}

$frmHost->addRow(array(
	new CVisibilityBox('visible[groups]', isset($visible['groups']), $grp_tb->getName(), _('Original')),
	_('Replace host groups')
), $grp_tb->get(_('In groups'), _('Other groups')));

$newgroupTB = new CTextBox('newgroup', $newgroup, ZBX_TEXTBOX_STANDARD_SIZE);
$newgroupTB->setAttribute('maxlength', 64);
$frmHost->addRow(array(new CVisibilityBox('visible[newgroup]', isset($visible['newgroup']),
	'newgroup', _('Original')), _('New host group')), $newgroupTB, 'new'
);

/*
 * Proxy
 */
$cmbProxy = new CComboBox('proxy_hostid', $proxy_hostid);
$cmbProxy->addItem(0, _('(no proxy)'));

$db_proxies = DBselect(
	'SELECT h.hostid,h.host'.
	' FROM hosts h'.
	' WHERE h.status IN ('.HOST_STATUS_PROXY_ACTIVE.','.HOST_STATUS_PROXY_PASSIVE.')'.
		' AND '.DBin_node('h.hostid').
	' ORDER BY h.host'
);
while ($db_proxy = DBfetch($db_proxies)) {
	$cmbProxy->addItem($db_proxy['hostid'], $db_proxy['host']);
}

$frmHost->addRow(array(
	new CVisibilityBox('visible[proxy_hostid]', isset($visible['proxy_hostid']), 'proxy_hostid', _('Original')),
	_('Monitored by proxy')),
	$cmbProxy
);

$cmbStatus = new CComboBox('status', $status);
$cmbStatus->addItem(HOST_STATUS_MONITORED, _('Monitored'));
$cmbStatus->addItem(HOST_STATUS_NOT_MONITORED, _('Not monitored'));

$frmHost->addRow(array(
	new CVisibilityBox('visible[status]', isset($visible['status']), 'status', _('Original')),
	_('Status')), $cmbStatus
);

/*
 * Link templates
 */
$template_table = new CTable();
$template_table->setAttribute('name', 'template_table');
$template_table->setAttribute('id', 'template_table');
$template_table->setCellPadding(0);
$template_table->setCellSpacing(0);

foreach ($templates as $id => $temp_name) {
	$frmHost->addVar('templates['.$id.']', $temp_name);
	$template_table->addRow(array(
		new CCheckBox('templates_rem['.$id.']', 'no', null, $id),
		$temp_name,
	));
}

$template_table->addRow(array(
	new CButton('add_template', _('Add'), "return PopUp('popup.php?dstfrm=".$frmHost->getName().
		"&dstfld1=new_template&srctbl=templates&srcfld1=hostid&srcfld2=host".
		url_param($templates, false, 'existed_templates')."', 450, 450)"
	),
	new CSubmit('unlink', _('Remove'))
));

$vbox = new CVisibilityBox('visible[template_table]', isset($visible['template_table']), 'template_table', _('Original'));
$vbox->setAttribute('id', 'cb_tpladd');
if (isset($visible['template_table_r'])) {
	$vbox->setAttribute('disabled', 'disabled');
}
$action = $vbox->getAttribute('onclick');
$action .= 'if ($("cb_tplrplc").disabled) { $("cb_tplrplc").enable(); } else { $("cb_tplrplc").disable(); }';
$vbox->setAttribute('onclick', $action);

$frmHost->addRow(array($vbox, _('Link additional templates')), $template_table, 'T');

/*
 * Relink templates
 */
$template_table_r = new CTable();
$template_table_r->setAttribute('name', 'template_table_r');
$template_table_r->setAttribute('id', 'template_table_r');
$template_table_r->setCellPadding(0);
$template_table_r->setCellSpacing(0);

foreach ($templates as $id => $temp_name) {
	$frmHost->addVar('templates['.$id.']', $temp_name);
	$template_table_r->addRow(array(
		new CCheckBox('templates_rem['.$id.']', 'no', null, $id),
		$temp_name,
	));
}

$template_table_r->addRow(array(
	new CButton('add_template', _('Add'), "return PopUp('popup.php?dstfrm=".$frmHost->getName().
		"&dstfld1=new_template&srctbl=templates&srcfld1=hostid&srcfld2=host".
		url_param($templates, false, 'existed_templates')."', 450, 450)"),
	new CSubmit('unlink', _('Remove'))
));

$vbox = new CVisibilityBox('visible[template_table_r]', isset($visible['template_table_r']), 'template_table_r', _('Original'));
$vbox->setAttribute('id', 'cb_tplrplc');
if (isset($visible['template_table'])) {
	$vbox->setAttribute('disabled', 'disabled');
}
$action = $vbox->getAttribute('onclick');
$action .= <<<JS
if($("cb_tpladd").disabled){
$("cb_tpladd").enable();
}
else{
$("cb_tpladd").disable();
}
$("clrcbdiv").toggle();
JS;
$vbox->setAttribute('onclick', $action);

$clear_cb = new CCheckBox('mass_clear_tpls', get_request('mass_clear_tpls', false));
$div = new CDiv(array($clear_cb, _('Clear when unlinking')));
$div->setAttribute('id', 'clrcbdiv');
$div->addStyle('margin-left: 20px;');
if (!isset($visible['template_table_r'])) {
	$div->addStyle('display: none;');
}

$frmHost->addRow(array($vbox, _('Replace linked templates'), $div), $template_table_r, 'T');

$cmbIPMIAuthtype = new CComboBox('ipmi_authtype', $ipmi_authtype);
$cmbIPMIAuthtype->addItem(IPMI_AUTHTYPE_DEFAULT, _('Default'));
$cmbIPMIAuthtype->addItem(IPMI_AUTHTYPE_NONE, _('None'));
$cmbIPMIAuthtype->addItem(IPMI_AUTHTYPE_MD2, _('MD2'));
$cmbIPMIAuthtype->addItem(IPMI_AUTHTYPE_MD5, _('MD5'));
$cmbIPMIAuthtype->addItem(IPMI_AUTHTYPE_STRAIGHT, _('Straight'));
$cmbIPMIAuthtype->addItem(IPMI_AUTHTYPE_OEM, _('OEM'));
$cmbIPMIAuthtype->addItem(IPMI_AUTHTYPE_RMCP_PLUS, _('RMCP+'));
$frmHost->addRow(array(
	new CVisibilityBox('visible[ipmi_authtype]', isset($visible['ipmi_authtype']), 'ipmi_authtype', _('Original')), _('IPMI authentication algorithm')),
	$cmbIPMIAuthtype
);

$cmbIPMIPrivilege = new CComboBox('ipmi_privilege', $ipmi_privilege);
$cmbIPMIPrivilege->addItem(IPMI_PRIVILEGE_CALLBACK, _('Callback'));
$cmbIPMIPrivilege->addItem(IPMI_PRIVILEGE_USER, _('User'));
$cmbIPMIPrivilege->addItem(IPMI_PRIVILEGE_OPERATOR, _('Operator'));
$cmbIPMIPrivilege->addItem(IPMI_PRIVILEGE_ADMIN, _('Admin'));
$cmbIPMIPrivilege->addItem(IPMI_PRIVILEGE_OEM, _('OEM'));
$frmHost->addRow(array(
	new CVisibilityBox('visible[ipmi_privilege]', isset($visible['ipmi_privilege']), 'ipmi_privilege', _('Original')), _('IPMI privilege level')),
	$cmbIPMIPrivilege
);

$frmHost->addRow(array(
	new CVisibilityBox('visible[ipmi_username]', isset($visible['ipmi_username']), 'ipmi_username', _('Original')), _('IPMI username')),
	new CTextBox('ipmi_username', $ipmi_username, ZBX_TEXTBOX_SMALL_SIZE)
);

$frmHost->addRow(array(
	new CVisibilityBox('visible[ipmi_password]', isset($visible['ipmi_password']), 'ipmi_password', _('Original')), _('IPMI password')),
	new CTextBox('ipmi_password', $ipmi_password, ZBX_TEXTBOX_SMALL_SIZE)
);

$inventoryModesCC = new CComboBox('inventory_mode', $inventory_mode, 'submit()');
$inventoryModesCC->addItem(HOST_INVENTORY_DISABLED, _('Disabled'));
$inventoryModesCC->addItem(HOST_INVENTORY_MANUAL, _('Manual'));
$inventoryModesCC->addItem(HOST_INVENTORY_AUTOMATIC, _('Automatic'));
$frmHost->addRow(array(
	new CVisibilityBox('visible[inventory_mode]', isset($visible['inventory_mode']), 'inventory_mode', _('Original')), _('Inventory mode')),
	$inventoryModesCC
);

$inventory_fields = getHostInventories();
$inventory_fields = zbx_toHash($inventory_fields, 'db_field');
if ($inventory_mode != HOST_INVENTORY_DISABLED) {
	foreach ($inventory_fields as $field => $caption) {
		$caption = $caption['title'];
		if (!isset($host_inventory[$field])) {
			$host_inventory[$field] = '';
		}

		$frmHost->addRow(
			array(
				new CVisibilityBox(
					'visible['.$field.']',
					isset($visible[$field]),
					'host_inventory['.$field.']',
					_('Original')
				),
				$caption
			),
			new CTextBox('host_inventory['.$field.']', $host_inventory[$field], ZBX_TEXTBOX_STANDARD_SIZE)
		);
	}
}

$frmHost->addItemToBottomRow(new CSubmit('masssave', _('Save')));
$frmHost->addItemToBottomRow(SPACE);
$frmHost->addItemToBottomRow(new CButtonCancel(url_param('config').url_param('groupid')));
return $frmHost;
?>
