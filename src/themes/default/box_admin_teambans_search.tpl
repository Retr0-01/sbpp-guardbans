<div align="center">
	<table width="50%" cellpadding="0" class="listtable" cellspacing="0">
		<tr class="sea_open">
			<td width="2%" height="16" class="listtable_top" colspan="3" style="text-align: center;"><b>Advanced Search</b> (Click)</td>
	  	</tr>
	  	<tr>
	  		<td>
	  		<div class="panel">
	  			<table width="100%" cellpadding="0" class="listtable" cellspacing="0">
			    <tr>
					<td class="listtable_1" width="8%" align="center"><input id="name" name="search_type" type="radio" value="name"></td>
			        <td class="listtable_1" width="26%">Nickname</td>
			        <td class="listtable_1" width="66%"><input class="textbox" type="text" id="name_" value="" onmouseup="$('name').checked = true" style="width: 87%;"></td>
				</tr>       
			    <tr>
			        <td class="listtable_1" width="8%" align="center"><input id="steamid" name="search_type" type="radio" value="steamid"></td>
			        <td class="listtable_1" width="26%">Steam ID</td>
			        <td class="listtable_1" width="66%"><input class="textbox" type="text" id="steamid_" value="" onmouseup="$('steamid').checked = true" style="width: 87%;"></td>
			    </tr>
			    <tr>
			        <td align="center" class="listtable_1" ><input id="reason" type="radio" name="search_type" value="radiobutton"></td>
			        <td class="listtable_1" >Reason</td>
			        <td class="listtable_1" ><input class="textbox" type="text" id="reason_" value="" onmouseup="$('reason').checked = true" style="width: 87%;"></td>
			    </tr>
                {if !$hideadminname}
			    <tr>
			    	<td class="listtable_1"  align="center"><input id="admin" name="search_type" type="radio" value="radiobutton"></td>
			        <td class="listtable_1" >Admin</td>
			        <td class="listtable_1" >
						<select class="select" id="ban_admin" onmouseup="$('admin').checked = true" style="width: 95%;">
							{foreach from=$admin_list item="admin"}
								<option label="{$admin.user}" value="{$admin.aid}">{$admin.user}</option>
					  		{/foreach}
						</select>
					</td> 
				</tr>
                {/if}
			    <tr>
			        <td colspan="4">{sb_button text="Search" onclick="search_teambans();" class="ok searchbtn" id="searchbtn" submit=false}</td>
			    </tr>
			   </table>
			   </div>
		  </td>
		</tr>
	</table>
</div>
{$server_script}
<script>InitAccordion('tr.sea_open', 'div.panel', 'mainwrapper');</script>
