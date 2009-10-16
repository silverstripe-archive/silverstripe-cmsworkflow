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

<h2>Embargo Expiry</h2>
<div id="embargoExpiry">
<p>These times are in local server time, which is $WorkflowTimezone</p>
<% if EmbargoField %>
	<p id="embargoExpiry-embargoStatus" style="display:<% if EmbargoDate %>block<% else %>none<% end_if %>">
		This page is currently scheduled to be published at <span id="embargoDate">$EmbargoDate</span>.
	</p>
<% end_if %>

<% if ExpiryField %>
	<p id="embargoExpiry-expiryStatus" style="display:<% if ExpiryDate %>block<% else %>none<% end_if %>">
		This page is currently scheduled to be unpublished at <span id="expiryDate">$ExpiryDate</span>.
	</p>
<% end_if %>

<% if ExpiryField || EmbargoField %>
	<% if Status != Scheduled %>
		<p id="startTimers">You need to click 'Approve' to start these timers in motion.</p>
	<% end_if %>
<% end_if %>

<% if CanChangeEmbargoExpiry %>
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

<% if EmbargoDate %><% else %>
	<script type="text/javascript">EmbargoExpiry.dButton('saveEmbargoButton');</script>
	<script type="text/javascript">EmbargoExpiry.dButton('resetEmbargoButton');</script>
<% end_if %>

</div>


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