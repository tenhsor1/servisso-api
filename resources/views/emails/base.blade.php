<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>@yield('header-title','Servisso')</title>
	<style type="text/css">
		/* Client-specific Styles */
		#outlook a {
			padding:0;
		}

		/* Force Outlook to provide a "view in browser" menu link. */
		body{
			width:100% !important;
			-webkit-text-size-adjust:100%;
			-ms-text-size-adjust:100%;
			margin:0;
			padding:0;
		}

		/* Prevent Webkit and Windows Mobile platforms from changing default font sizes, while not breaking desktop design. */
		.ExternalClass {
			width:100%;
		}

		/* Force Hotmail to display emails at full width */
		.ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {
			line-height: 100%;
		}

		/* Force Hotmail to display normal line spacing.*/
		#backgroundTable {
			margin:0; padding:0; width:100% !important; line-height: 100% !important;
		}

		img {
			outline:none;
			text-decoration:none;
			border:none;
			-ms-interpolation-mode: bicubic;
		}

		a img {
			border:none;
		}

		.image_fix {
			display:block;
		}

		p {
			margin: 0px 0px !important;
		}

		table td {
			border-collapse: collapse;
		}

		table {
			border-collapse:collapse;
			mso-table-lspace:0pt;
			mso-table-rspace:0pt;
		}

		a {
			text-underline:none!important;
			text-decoration: none;
			text-decoration:none!important;
		}

		/*STYLES*/
		table[class=full] {
			width: 100%;
			clear: both;
		}

		/*IPAD STYLES*/
		@media only screen and (max-width: 640px) {
				a[href^="tel"], a[href^="sms"] {
				text-decoration: none;
				color: #ffffff; /* or whatever your want */
				pointer-events: none;
				cursor: default;
			}

			.mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {
				text-decoration: default;
				color: #ffffff !important;
				pointer-events: auto;
				cursor: default;
			}

			table[class=devicewidth] {
				width: 440px!important;text-align:center!important;
			}

			table[class=devicewidthinner] {
				width: 420px!important;
				text-align:center!important;
			}

			table[class="sthide"]{
				display: none!important;
			}

			img[class="bigimage"]{
				width: 420px!important;
				height:219px!important;
			}

			img[class="col2img"]{
				width: 420px!important;
				height:258px!important;
			}

			img[class="image-banner"]{
				width: 440px!important;
				height:106px!important;
			}

			td[class="menu"]{
				text-align:center !important;
				padding: 0 0 10px 0 !important;
			}

			td[class="logo"]{
				padding:10px 0 5px 0!important;
				margin: 0 auto !important;
			}

			img[class="logo"]{
				padding:0!important;
				margin: 0 auto !important;
			}
		}

		/*IPHONE STYLES*/
		@media only screen and (max-width: 480px) {
			a[href^="tel"], a[href^="sms"] {
				text-decoration: none;
				color: #ffffff; /* or whatever your want */
				pointer-events: none;
				cursor: default;
			}

			.mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {
				text-decoration: default;
				color: #ffffff !important;
				pointer-events: auto;
				cursor: default;
			}

			table[class=devicewidth] {
				width: 280px!important;
				text-align:center!important;
			}

			table[class=devicewidthinner] {
				width: 260px!important;
				text-align:center!important;
			}

			table[class="sthide"]{
				display: none!important;
			}

			img[class="bigimage"]{
				width: 260px!important;
				height:136px!important;
			}

			img[class="col2img"]{
				width: 260px!important;
				height:160px!important;
			}

			img[class="image-banner"]{
				width: 280px!important;
				height:68px!important;
			}
		}
	</style>
</head>
<body>
	<table width="100%" bgcolor="#f0f0f0" cellpadding="0" cellspacing="0" border="0" st-sortable="header">
		<tbody>
			<tr>
				<td>
					<table width="580" bgcolor="#22292e" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth" hasbackground="true">
						<tbody>
							<tr>
								<td>
									<table width="290" cellpadding="0" cellspacing="0" border="0" align="left" class="devicewidth" bgcolor="#22292e" border="1">
										<tbody>
											<tr>
												<td valign="middle" width="270" style="padding: 10px 0 10px 20px;" class="logo">
													<div class="imgpop">
														 <a href="{{ $logoUrl or 'www.servisso.com.mx'}}">
															<!-- LOGO -->
															<img src="http://images.servisso.com/emails/logo_email.png" width="100" alt="logo" border="0" style="display:block; border:none; outline:none; text-decoration:none;" st-image="edit" class="logo" id="nsn1vloysree9udi">
														</a>
													</div>
												</td>
											</tr>
										</tbody>
									</table>
									<!-- menu -->
									<table width="290" cellpadding="0" cellspacing="0" border="0" align="right" class="devicewidth" bgcolor="#22292e">
										<tbody>
											<tr>
												<td width="270" valign="middle" style="font-family: Helvetica, Arial, sans-serif;font-size: 14px; color: #ffffff;line-height: 24px; padding: 10px 0;" align="right" class="menu" st-content="menu">
													<p>
														<span style="color: rgb(43, 187, 196);">
															<a href="{{ $menuItemHow or 'www.servisso.com.mx/profesionales' }}">
																<span style="color: rgb(43, 187, 196);">COMO FUNCIONA</span>
															</a>
														</span> |
														<span style="color: rgb(43, 187, 196);">
															<a href="{{ $menuItemHome or 'www.servisso.com.mx' }}">
																<span style="color: rgb(43, 187, 196);">INICIO</span>
															</a>
														</span>
													</p>
												</td>
												<td width="20">
												</td>
											</tr>
										</tbody>
									</table>
									<!-- End of Menu -->
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>
	<table width="100%" bgcolor="#f0f0f0" cellpadding="0" cellspacing="0" border="0" st-sortable="full-text">
		<tbody>
			<tr>
				<td>
					<table bgcolor="#ffffff" width="580" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth" hasbackground="true">
						<tbody>
							<tr>
								<td width="100%" height="30"></td>
							</tr>
							<tr>
								<td>
									<table width="540" align="center" cellpadding="0" cellspacing="0" border="0" class="devicewidthinner">
										<tbody>
										<!-- TITLE -->
											<tr>
												<td style="font-family: Helvetica, arial, sans-serif; font-size: 18px; color: #333333; text-align:center;line-height: 20px;" st-title="fulltext-title">
													<p>
														@yield('main-title','Servisso')
													</p>
												</td>
											</tr>
											<tr>
												<td height="20"></td>
											</tr>
											@yield('content','Servisso')
											<!-- SPACE BEFORE BUTTON -->
											<tr>
												<td width="100%" height="5"></td>
											</tr>
											@yield('content-button','Servisso')
											<tr>
												<!-- SPACE AFTER BUTTON -->
												<td width="100%" height="30"></td>
											</tr>
											<tr>
												<td style="font-family: Helvetica, arial, sans-serif; font-size: 12px; color: #95a5a6;text-align:center">
													<div>
														Para cualquier duda o sugerencia visitanos en <a href="https://www.servisso.com/">www.servisso.com</a>
													</div>
													<div>
														Si ya no quieres recibir más notificaciones da <a href="#" style="color:gray">click aqui</a>
													</div>
												</td>
											</tr>
											<tr>
												<!-- FINAL SPACE -->
												<td width="100%" height="30"></td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>
	<table border="0" cellpadding="0" cellspacing="0" class="templateRow" width="100%" style="border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0">
		<tbody>
			<tr>
				<td class="rowContainer kmFloatLeft" valign="top" style="border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0">
					<table border="0" cellpadding="0" cellspacing="0" class="kmButtonBarBlock" width="100%" style="border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0">
						<tbody class="kmButtonBarOuter">
							<tr>
								<td class="kmButtonBarInner" align="center" valign="top" style="border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0;padding-top:20px;padding-bottom:9px;background-color:#eeeeee;padding-left:9px;padding-right:9px;">
									<table border="0" cellpadding="0" cellspacing="0" class="kmButtonBarContentContainer" width="100%" style="border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0">
										<tbody>
											<tr>
												<td align="center" style="border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0;padding-left:9px;padding-right:9px;">
													<table border="0" cellpadding="0" cellspacing="0" class="kmButtonBarContent" style='border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0;font-family:"Helvetica Neue", Arial'>
														<tbody>
															<tr>
																<td align="center" valign="top" style="border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0">
																	<table border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0">
																		<tbody>
																			<tr>
																				<td valign="top" style="border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0">
																					<!-- TWITTER -->
																					<table align="left" border="0" cellpadding="0" cellspacing="0" class="" style="border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0">
																						<tbody>
																							<tr>
																								<td align="center" valign="top" style="border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0;padding-right:10px;">
																									<a href="https://twitter.com/ServissoCom" target="_blank" style="word-wrap:break-word;color:#334454;font-weight:normal;text-decoration:underline">
																										<img src="http://images.servisso.com/emails/twitter_email.png" alt="Twitter" class="kmButtonBlockIcon" width="48" style="border:0;height:auto;line-height:100%;outline:none;text-decoration:none;width:48px; max-width:48px; display:block;" />
																									</a>
																								</td>
																							</tr>
																						</tbody>
																					</table>
																					<!-- FACEBOOK -->
																					<table align="left" border="0" cellpadding="0" cellspacing="0" class="" style="border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0">
																						<tbody>
																							<tr>
																								<td align="center" valign="top" style="border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0;padding-right:10px;">
																									<a href="https://facebook.com/ServissoCom" target="_blank" style="word-wrap:break-word;color:#334454;font-weight:normal;text-decoration:underline">
																										<img src="http://images.servisso.com/emails/facebook_email.png" alt="Facebook" class="kmButtonBlockIcon" width="48" style="border:0;height:auto;line-height:100%;outline:none;text-decoration:none;width:48px; max-width:48px; display:block;" />
																									</a>
																								</td>
																							</tr>
																						</tbody>
																					</table>
																					<!-- G+  -->
																					<table align="left" border="0" cellpadding="0" cellspacing="0" class="" style="border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0">
																						<tbody>
																							<tr>
																								<td align="center" valign="top" style="border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0;">
																									<a href="https://plus.google.com/ServissoCom" target="_blank" style="word-wrap:break-word;color:#334454;font-weight:normal;text-decoration:underline">
																										<img src="http://images.servisso.com/emails/google_plus_email.png" alt="Google Plus" class="kmButtonBlockIcon" width="48" style="border:0;height:auto;line-height:100%;outline:none;text-decoration:none;width:48px; max-width:48px; display:block;" />
																									</a>
																								</td>
																							</tr>
																						</tbody>
																					</table>
																				</td>
																			</tr>
																		</tbody>
																	</table>
																</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>
	@yield('footer-sign','')
</body>
</html>