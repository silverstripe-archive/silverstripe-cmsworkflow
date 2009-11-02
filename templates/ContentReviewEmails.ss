<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
	</head>
	<body>
		<table id="Content" cellspacing="0" cellpadding="0">
			<tbody>
				<tr>
					<td scope="row" colspan="2" class="typography">
						<h2><% _t('ContentReviewEmails.EMAIL_HEADING','Page due for review') %></h2>
						
						<p>The page $Page.Title is due for review today by you</p>
						
						<h2>Actions</h2>
						<ul>
							<li><a href="$PageCMSLink"><% _t('ContentReviewEmails.REVIEWPAGELINK','Review the page in the CMS') %></a></li>
							<li><a href="$LiveSiteLink"><% _t('ContentReviewEmails.VIEWPUBLISHEDLINK','View this page on your website') %></a></li>
						</ul>
					</td>
				</tr>
			</tbody>
		</table>
	</body>
</html>