<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<body>
		<table id="Content" cellspacing="0" cellpadding="0">
			<tbody>
				<tr>
					<td scope="row" colspan="2" class="typography">
						Hi $Recipient.Name,<br />
						<p>
						$Sender.Name has changed the workflow status on "<a href="$LiveSiteLink">$Page.Title</a>".
						</p>
						<ul>
							<li><a href="$PageCMSLink">Review the page in the CMS</a></li>
							<li><a href="$LiveSiteLink">View the published site</a></li>
							<li><a href="$DiffCMSLink">View unpublished changes</a></li>
						</ul>
						<br />
						Thanks.
					</td>
				</tr>
			</tbody>
		</table>
	</body>
</html>