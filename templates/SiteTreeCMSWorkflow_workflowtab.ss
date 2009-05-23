<% require themedCSS(CMSWorkflow) %>
<div id="CMSWorkflowTab">
<% control OpenWorkflowRequest %>
<h1>Change $StatusDescription</h1>
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
<% end_control %>

<h2>Actions</h2>
<p>
	Comment:<br />
	<textarea id="Form_EditForm_WorkflowComment" name="WorkflowComment"></textarea>
</p>
<p id="WorkflowActions">
<% control WorkflowActions %>
	<input id="Form_EditForm_$Action" name="$Action" type="submit" value="$Title" />
<% end_control %>
</p>
</div>