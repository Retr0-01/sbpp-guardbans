<form action="" method="post">
	<div id="admin-page-content">
		<div id="0">
			<div id="msg-green" style="display:none;">
				<i><img src="./images/yay.png" alt="Warning" /></i>
				<b>Teamban Updated</b>
				<br />
				The teamban details have been updated.<br /><br />
				<i>Redirecting back to bans page</i>
			</div>
			<div id="add-group">
			<h3>Teamban Details</h3>
			<h4>WARNING: Editing a teamban's length will also reset the time left to the newest duration set.</h4>
			For more information or help regarding a certain subject move your mouse over the question mark.<br /><br />
			<input type="hidden" name="insert_type" value="add">
			<table width="90%" border="0" style="border-collapse:collapse;" id="group.details" cellpadding="3">
			  <tr>
			    <td valign="top" width="35%">
				  <div class="rowdesc">
				    -{help_icon title="Player name" message="This is the name of the player that was teambanned."}-Player name
				  </div>
				</td>
			    <td>
				  <div align="left">
			        <input type="text" class="submit-fields" id="name" name="name" value="-{$ban_name}-" />
			      </div>
			      <div id="name.msg" class="badentry"></div></td>
			  </tr>
 		  </tr>
  		<tr>
    		<td valign="top">
    			<div class="rowdesc">
    				-{help_icon title="Steam ID" message="This is the Steam ID of the player that is teambanned. You may want to type a Community ID either."}-Steam ID
    			</div>
    		</td>
    		<td>
    			<div align="left">
      			<input value="-{$ban_authid}-" type="text" TABINDEX=3 class="submit-fields" id="steam" name="steam" />
    			</div>
    			<div id="steam.msg" class="badentry"></div>
    		</td>
  		</tr>
 		  <tr>
    		<td valign="top" width="35%">
    			<div class="rowdesc">
    				-{help_icon title="Reason" message="The reason that this player was teambanned."}-Reason
    			</div>
    		</td>
    		<td>
    			<div align="left">
    				<select id="listReason" name="listReason" TABINDEX=4 class="submit-fields" onChange="changeReason(this[this.selectedIndex].value);">
    					<option value="" selected> -- Select Reason -- </option>
					<optgroup label="General">
						<option value="Freehit">Freehit</option>
						<option value="Freekill">Freekill</option>
						<option value="LR Denial">LR Denial</option>
						<option value="DR Denial">DR Denial</option>
						<option value="Mass Freehit">Mass Freehit</option>
						<option value="Attempted Mass Freekill">Attempted Mass Freekill</option>
						<option value="Mass Freekill">Mass Freekill</option>
					</optgroup>
					<optgroup label="Behavior">
						<option value="Disobeying Warden">Disobeying Warden</option>
						<option value="No Mic">No Mic</option>
						<option value="Assisting Rebellers">Assisting Rebellers</option>
						<option value="Not Assisting Warden">Not Assisting Warden</option>
						<option value="Armory Camping">Armory Camping</option>
						<option value="Medic Camping">Medic Camping</option>
					</optgroup>
					<optgroup label="Warden">
						<option value="Illegal FF">Illegal FF</option>
						<option value="Illegal CC">Illegal CC</option>
						<option value="Illegal Orders">Illegal Orders</option>
						<option value="Force Freeday">Force Freeday</option>
						<option value="Favoritism">Favoritism</option>
					</optgroup>
					-{if $customreason}-
					<optgroup label="Custom">
					-{foreach from="$customreason" item="creason"}-
						<option value="-{$creason}-">-{$creason}-</option>
					-{/foreach}-
					</optgroup>
					-{/if}-
					<option value="other">Other Reason</option>
				</select>
				<div id="dreason" style="display:none;">
     					<textarea class="submit-fields" TABINDEX=4 cols="30" rows="5" id="txtReason" name="txtReason"></textarea>
     				</div>
    			</div>
    			<div id="reason.msg" class="badentry"></div>
    		</td>
			 <tr>
			    <td valign="top" width="35%"><div class="rowdesc">-{help_icon title="Teamban Length" message="Select how long you want to ban this person for."}-Teamban Length </div></td>
			    <td><div align="left">
			      	<select id="banlength" name="banlength" TABINDEX=5 class="submit-fields">
						<option value="0">Permanent</option>
							<optgroup label="minutes">
								<option value="1">1 minute</option>
								<option value="5">5 minutes</option>
								<option value="10">10 minutes</option>
								<option value="15">15 minutes</option>
								<option value="30">30 minutes</option>
								<option value="45">45 minutes</option>
							</optgroup>
							<optgroup label="hours">
								<option value="60">1 hour</option>
								<option value="120">2 hours</option>
								<option value="180">3 hours</option>
								<option value="240">4 hours</option>
								<option value="480">8 hours</option>
								<option value="720">12 hours</option>
							</optgroup
							><optgroup label="days">
								<option value="1440">1 day</option>
								<option value="2880">2 days</option>
								<option value="4320">3 days</option>
								<option value="5760">4 days</option>
								<option value="7200">5 days</option>
								<option value="8640">6 days</option>
							</optgroup>
							<optgroup label="weeks">
								<option value="10080">1 week</option>
								<option value="20160">2 weeks</option>
								<option value="30240">3 weeks</option>
							</optgroup>
							<optgroup label="months">
								<option value="43200">1 month</option>
								<option value="86400">2 months</option>
								<option value="129600">3 months</option>
								<option value="259200">6 months</option>
								<option value="518400">12 months</option>
							</optgroup>
					</select>
			    </div><div id="length.msg" class="badentry"></div></td>
			  </tr>
			  <tr>
			    <td>&nbsp;</td>
			    <td>
			      	-{sb_button text="Save Changes" class="ok" id="editban" submit=true}-
			     	 &nbsp;
			     	-{sb_button text="Back" onclick="history.go(-1)" class="cancel" id="back" submit=false}-
			      </td>
			  </tr>
			</table>
			</div>
		</div>
	</div>
</form>
