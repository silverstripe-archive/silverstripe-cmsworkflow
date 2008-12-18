<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<body>
		<table id="Content" cellspacing="0" cellpadding="0">
			<tbody>
				<tr>
					<td scope="row" colspan="2" class="typography">
						<% sprintf(_t('WorkflowRequest.EMAILGREETING','Hi %s'),$Recipient.Name) %>,<br />
						<p>
						<% sprintf(_t('WorkflowRequest.EMAILRECENTLYUPDATED1','%s has recently updated the page titled'),$Sender.Name) %> "<a href="$LiveSiteLink">$Page.Title</a>",
						<% _t('WorkflowRequest.EMAILRECENTLYUPDATED2','and would like to have the changes published.') %>
						</p>
						<ul>
							<li><a href="$PageCMSLink"><% _t('WorkflowRequest.REVIEWANDPUBLISHPAGELINK','Review and publish the page in the CMS') %></a></li>
							<li><a href="$StageSiteLink"><% _t('WorkflowRequest.VIEWCHANGEDDRAFTLINK','View the changed draft') %></a></li>
							<li><a href="$LiveSiteLink"><% _t('WorkflowRequest.VIEWPUBLISHEDLINK','View the published site') %></a></li>
							<% if DiffLink %>
								<li><a href="$DiffLink"><% _t('WorkflowRequest.COMPAREDRAFTLIVELINK','Compare changes between live and the changed draft') %></a></li>
							<% end_if %>
						</ul>
						<br />
						<% _t('WorkflowRequest.EMAILTHANKS','Thanks.') %>
					</td>
				</tr>
			</tbody>
		</table>
	</body>
</html>