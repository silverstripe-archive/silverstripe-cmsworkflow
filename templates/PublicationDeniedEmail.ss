<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<body>
		<table id="Content" cellspacing="0" cellpadding="0">
			<tbody>
				<tr>
					<td scope="row" colspan="2" class="typography">
						<% sprintf(_t('WorkflowRequest.EMAILGREETING','Hi %s'),$Recipient.Name) %>,<br />
						<p>
							<% sprintf(_t('WorkflowRequest.EMAILDENIEDPUBLICATION','%s has denied your publication request on '),$Sender.Name) %> "<a href="$LiveSiteLink">$Page.Title</a>".
						</p>
						<ul>
							<li><a href="$PageCMSLink"><% _t('WorkflowRequest.REVIEWPAGELINK','Review the page in the CMS') %></a></li>
							<li><a href="$LiveSiteLink"><% _t('WorkflowRequest.VIEWPUBLISHEDLINK','View this page on your website') %></a></li> %></a></li>
						</ul>
						<br />
						<% _t('WorkflowRequest.EMAILTHANKS','Thanks.') %>
					</td>
				</tr>
			</tbody>
		</table>
	</body>
</html>