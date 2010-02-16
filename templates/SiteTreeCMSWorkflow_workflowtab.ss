<% require themedCSS(CMSWorkflow) %>

<div id="CMSWorkflowTab">
	<% control OpenWorkflowRequest %>
		<h1>Change $StatusDescription</h1>
		<input type="hidden" id="WorkflowRequest_ID" value="$ID" />
		<p>$Author.FirstName $Author.Surname has requested that a change to the site be published.</p>

		<h2>Changes</h2>
		<dl id="CMSWorkflowDiff">
			<% control Diff.ChangedFields %>
				<dt>$Title</dt>
				<dd>$Diff</dd>
			<% end_control %>
		</dl>

		<h2>Discussion</h2>
		<ul id="CMSWorkflowChanges">
			<% control Changes %>
				<li>
					<% if Status %>
						<em class="workflowStatusChange">Changed status to $StatusDescription</em>
					<% end_if %>
					$Author.FirstName $Author.Surname ($Author.Email) <i>$Created.Nice ($Created.Ago)</i><br />
					<div class="workflowComment">$Comment</div>
				</li>
			<% end_control %>
		</ul>

		<style type="text/css">
			div#futurePublishing div.popupdatetime {
				display:inline;
			}
			div#futurePublishing div.popupdatetime ul {
				float:left;
				margin-right:10px;
			}
		</style>

		<div id="embargoExpiry">
			<% if ClassName == WorkflowDeletionRequest %>
				<% if Page.canApprove %>
					<table>
						<tr>
							<td>
								<input id="deleteImmediate" <% if ExpiryDate %><% else %>checked="true"<% end_if %> type="radio" name="DeletionScheduling" value="immediate" />
							</td>
							<td>Action this request when the publish button is pushed</td>
						</tr>
						<tr>
							<td>
								<input id="deleteLater" <% if ExpiryDate %>checked="true"<% end_if %> type="radio" name="DeletionScheduling" value="scheduled"/>
							</td>
							<td>Schedule this page to expire at a later date</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td>
								<div id="expiryField" style="display:<% if ExpiryDate %>block<% else %>none<% end_if %>">
									$ExpiryField
								</div>
							</td>
						</tr>
					</table>
					
					<% if ExpiryField %>
						<p id="embargoExpiry-expiryStatus" style="display:<% if ExpiryDate %>block<% else %>none<% end_if %>">
							This page is currently scheduled to be unpublished at <span id="expiryDate">$ExpiryDate</span>.
						</p>
					<% end_if %>

				<% end_if %>
			<% end_if %>

			<% if ClassName == WorkflowPublicationRequest %>
				<% if ExpiryField || EmbargoField %>
					<% if Status = AwaitingApproval %>
						<h2>Embargo Expiry</h2>
						<p>These times are in local server time, which is $WorkflowTimezone</p>
					<% end_if %>
				<% end_if %>
				
				<% if EmbargoField %>
					<p id="embargoExpiry-embargoStatus" style="display:<% if EmbargoDate %>block<% else %>none<% end_if %>">
						This page is currently scheduled to be published at <span id="embargoDate">$EmbargoDate.Full</span>.
					</p>
				<% end_if %>
				
				<% if ExpiryField %>
					<p id="embargoExpiry-expiryStatus" style="display:<% if ExpiryDate %>block<% else %>none<% end_if %>">
						This page is currently scheduled to be unpublished at <span id="expiryDate">$ExpiryDate.Full</span>.
					</p>
				<% end_if %>

				<% if EmbargoField || ExpiryField %>
					<% if Status = AwaitingApproval %>
						<p id="startTimers">You need to click 'Approve' to start these timers in motion.</p>
					<% end_if %>
					<% if Status = Approved %>
						<% if EmbargoDate %>
						<% else_if ExpiryDate %>
							<p id="startTimers">You need to click 'Publish changes' to start this timer in motion.</p>
						<% end_if %>
					<% end_if %>
				<% end_if %>

				<% if CanChangeEmbargoExpiry %>
					$TZConverter
					<% if EmbargoField %>
						<p>
							$EmbargoField
							<input type="button" id="saveEmbargoButton" class="action" onclick="EmbargoExpiry.save('embargo', this);" value="Set embargo date">
							<input type="button" id="resetEmbargoButton" class="action" onclick="EmbargoExpiry.reset('embargo', this);" value="Reset">
						</p>
					<% end_if %>
					<% if ExpiryField %>
						<p>
							$ExpiryField
							<input type="button" id="saveExpiryButton" class="action" onclick="EmbargoExpiry.save('expiry', this);" value="Set expiry date">
							<input type="button" id="resetExpiryButton" class="action" onclick="EmbargoExpiry.reset('expiry', this);" value="Reset">
						</p>
					<% end_if %>
				<% end_if %>
			<% end_if %>
		</div>

		<% control Page %>
			<% if BackLinkTracking %>
				<% if BackLinkTracking.Count %>
					<p>There are $BackLinkTracking.Count page(s) that link to here.</p>
				<% end_if %>
			<% end_if %>
		<% end_control %>

		<h2>Actions</h2>
		<p>
			Comment:<br />
			<textarea id="Form_EditForm_WorkflowComment" name="WorkflowComment" rows="6"></textarea>
		</p>

	<% end_control %>
	
	<p id="WorkflowActions">
		<% control WorkflowActions %>
			<input id="Form_EditForm_$Action" name="$Action" type="submit" value="$Title" />
		<% end_control %>
	</p>
</div>
