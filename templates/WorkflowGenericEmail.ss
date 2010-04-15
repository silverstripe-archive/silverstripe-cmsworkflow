<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<style type="text/css"><!--
		td #CMSWorkflowChanges li {
			background:#E9E9E9;
			border: 1px #CCC solid;
			padding: 3px;
			margin: 10px 0;
		}
		td #CMSWorkflowChanges li .workflowStatusChange {
			font-weight: bold;
			font-style: italic;
			float: right;
		}
		td #CMSWorkflowChanges li .workflowComment {
			margin-top: 0.5em;
			font-size: 1.2em;
		}

		td #CMSWorkflowDiff dt {
			color: #666;
			font-size: 11px;
			font-weight: normal;
			margin-top: 6px;
		}

		td #CMSWorkflowDiff dd {
			background:#E9E9E9;
			border: 1px #CCC solid;
			padding: 3px;
		}

		td #CMSWorkflowTab h2 {
			margin-bottom: -10px;
		}
	--></style>
	</head>
	<body>
		<table id="Content" cellspacing="0" cellpadding="0">
			<tbody>
				<tr>
					<td scope="row" colspan="2" class="typography">
						<% sprintf(_t('WorkflowRequest.EMAILGREETING','Hi %s'),$Recipient.Title) %>,<br />
						
						<p>$Sender.Title has made changes to $Page.Title and has $RequestedAction.</p>
						
						<% if Comment %>
						<h2><% _t('WorkflowRequest.COMMENT_HEADING','Comment') %></h2>
						<p>$Comment</p>
						<% end_if %>
						
						<% control Workflow %>
						<% if Diff.ChangedFields %>
						<dl id="CMSWorkflowDiff">
						<% control Diff.ChangedFields %>
							<dt>$Title</dt>
							<dd>$Diff</dd>
						<% end_control %>
						</dl>
						<% end_if %>
						<% end_control %>
						
						<h2>Actions</h2>
						<ul>
							<li><a href="$PageCMSLink"><% _t('WorkflowRequest.VIEWCMSLINK','View this page in the CMS to action the request') %></a></li>
							<li><a href="$LiveSiteLink"><% _t('WorkflowRequest.VIEWPUBLISHEDLINK','View this page on the website') %></a></li>
						</ul>
						<br />
						<% sprintf(_t('WorkflowRequest.EMAILTHANKS','Thanks, the %s web team'), $SiteConfig.Title) %>
					</td>
				</tr>
			</tbody>
		</table>
	</body>
</html>