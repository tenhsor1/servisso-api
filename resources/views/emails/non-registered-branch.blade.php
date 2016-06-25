@extends('emails.base')

@section('header-title', 'Servisso - Has sido seleccionado')

@section('main-title', '¡Alguien requiere de tus servicios!')

@section('content')
	<tr>
		<td style="font-family: Helvetica, arial, sans-serif; font-size: 14px; color: #95a5a6;background-color:#ecf9fa;padding:20px" st-content="fulltext-paragraph">                                            
			<div style="font-weight:bold">
				{{$client_name}} 
				<span style="color: #d0cece;font-size:12px">
					{{$created_date}}
				</span>
			</div>
			<div>
				{{$problem_description}}
			</div>
		</td>
	</tr>
	<tr>
		<td width="100%" height="10"></td>
	</tr>
	<!-- MESSAGE -->
	<tr>
		<td style="font-family: Helvetica, arial, sans-serif; font-size: 14px; color: #95a5a6;padding:20px;line-height:20px" st-content="fulltext-paragraph">
		<b style="color:#22292e">Eres parte de los seleccionados</b> 
		de esta nueva plataforma, para formar parte de nuesta comunidad de profesionales y para realizar el trabajo solicitado, te invitamos a registrarte, es fácil y rápido!
		</td>
	</tr>
@stop

@section('content-button')
	<tr>
		<td>
			<table height="36" align="center" valign="middle" border="0" cellpadding="0" cellspacing="0" class="tablet-button" st-button="edit" style="border-radius: 4px; font-size: 13px; font-family: Helvetica, arial, sans-serif; text-align: center; color: rgb(255, 255, 255); font-weight: 300; padding-left: 25px; padding-right: 25px; background-color: rgb(43, 186, 196); background-clip: padding-box;" bgcolor="#2bbac4">
				<tbody>
					<tr>
						<td style="padding-left:18px; padding-right:18px;font-family:Helvetica, arial, sans-serif; text-align:center;  color:#ffffff; font-weight: 300;" width="auto" align="center" valign="middle" height="36">
							<a style="color: #ffffff; text-align:center;text-decoration: none;" href="{{ $btn_url }}?email={{$branch_email}}" st-content="download" tabindex="-1">
								<span style="color: #ffffff; font-weight: 300;">
									Registrarme
								</span>
							</a>
						</td>
					</tr>
				</tbody>
			</table>
		</td>
	</tr>
@stop