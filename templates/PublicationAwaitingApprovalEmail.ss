<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<body>
		<table id="Content" cellspacing="0" cellpadding="0">
			<tbody>
				<tr>
					<td scope="row" colspan="2" class="typography">
						Hi $Recipient.Name,<br />
						<p>
						$Sender.Name has recently updated the page titled "<a href="$LiveSiteLink">$Page.Title</a>" 
						and would like to have the changes published.
						</p>
						<ul>
							<li><a href="$PageCMSLink">Publish the page in the CMS</a></li>
							<li><a href="$StageSiteLink">View the changed draft</a></li>
							<li><a href="$LiveSiteLink">View the published site</a></li>
							<% if DiffLink %><li><a href="$DiffLink">Compare changes between live and the changed draft</a></li><% end_if %>
						</ul>
						<br />
						Thanks.
					</td>
				</tr>
			</tbody>
		</table>
	</body>
</html>